<?php

namespace Huying\Sms;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * 短信供应商抽象类
 *
 * Class AbstractProvider
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * GET 请求
     *
     * @var string
     */
    const METHOD_GET = 'GET';

    /**
     * POST 请求
     *
     * @var string
     */
    const METHOD_POST = 'POST';

    /**
     * 短信接口参数的键
     *
     * @var string
     */
    const PROVIDER_OPTIONS = '短信接口';

    /**
     * 短信参数的键
     *
     * @var string
     */
    const MESSAGE_OPTIONS = '短信';

    /**
     * 优先级
     *
     * 当在Pool中注册多个Provider时，优先级高的先使用
     *
     * @var int
     */
    protected $priority;

    /**
     * 发送的请求
     *
     * @var Request
     */
    protected $request;

    /**
     * 接口回复
     *
     * @var Response
     */
    protected $response;

    /**
     * HTTP 客户端
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * 构造方法
     *
     * 需要传入短信接口相关的参数
     *
     * @param array $options
     * @param array $collaborators
     */
    public function __construct($options = [], array $collaborators = [])
    {
        $this->assertRequiredOptions(static::PROVIDER_OPTIONS, $options);

        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->{$option} = $value;
            }
        }

        if (empty($collaborators['httpClient'])) {
            $collaborators['httpClient'] = new HttpClient();
        }
        $this->setHttpClient($collaborators['httpClient']);
    }

    /**
     * 获取当前的 HTTP 客户端
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * 设置当前的 HTTP 客户端
     *
     * @param HttpClient $httpClient
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * 返回短信接口必须的参数
     *
     * @param string $key
     * @return array
     */
    abstract protected function getRequiredOptions($key);

    /**
     * 验证是否必须的参数都存在
     *
     * @param string $key
     * @param  array $options
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function assertRequiredOptions($key, array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions($key)), $options);
        if ($missing) {
            throw new \InvalidArgumentException(
                '参数不完整，请指定'.$key.'参数: '.implode(', ', array_keys($missing))
            );
        }
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function send(Message $message)
    {
        $this->assertRequiredOptions(static::MESSAGE_OPTIONS, $message->toArray());
        $protocolVersion = $this->getProtocolVersion();
        $url = $this->getUrl($message);
        $method = $this->getRequestMethod();
        $requestHeaders = $this->getRequestHeaders();
        $requestPayload = $this->getRequestPayload($message);

        $request = $this->createRequest(
            $method,
            $url,
            $requestHeaders,
            $requestPayload,
            $protocolVersion,
            $message
        );
        $message->setHttpRequest($request);

        $httpClient = $this->httpClient;
        $response = $httpClient->send($request);
        try {
            $parsedResponse = $this->handleResponse($response);
            $message->setResponse($parsedResponse);
            $message->setStatus(MessageStatus::STATUS_SENT);
        } catch (ProviderException $exception) {
            $message->setResponse($exception->getResponseBody());
            $message->setError($exception);
            $message->setStatus(MessageStatus::STATUS_FAILED);
        }
        $message->setHttpResponse($response);

        if (isset($exception)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 获取 HTTP 协议版本号
     *
     * @return string
     */
    protected function getProtocolVersion()
    {
        return '1.1';
    }

    /**
     * 返回请求链接
     *
     * @param Message $message
     * @return string
     * @throws \RuntimeException
     */
    abstract protected function getUrl(Message $message);

    /**
     * 返回请求的方法
     *
     * @return string HTTP 方法
     */
    abstract protected function getRequestMethod();

    /**
     * 返回请求短信接口时的 headers
     *
     * @return array
     */
    abstract protected function getRequestHeaders();

    /**
     * 返回请求短信接口时的 payload
     *
     * @param Message $message
     * @return string
     * @throws \RuntimeException
     */
    abstract protected function getRequestPayload(Message $message);

    /**
     * 创建向短信接口的发送请求
     *
     * @param null|string $method
     * @param null|string $url
     * @param array  $requestHeaders
     * @param $requestPayload
     * @param string $protocolVersion
     * @return Request
     * @throws \InvalidArgumentException
     */
    protected function createRequest($method, $url, $requestHeaders, $requestPayload, $protocolVersion)
    {
        $request = new Request(
            $method,
            $url,
            $requestHeaders,
            $requestPayload,
            $protocolVersion
        );

        return $request;
    }

    /**
     * 处理短信接口的返回结果
     *
     * @param Response $response
     * @return array 解析过的返回内容
     * @throws ProviderException
     */
    abstract protected function handleResponse(Response $response);

    /**
     * 解析 JSON 格式的字符串
     *
     * @param string $content
     * @return array
     * @throws \UnexpectedValueException
     */
    protected function parseJson($content)
    {
        $content = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \UnexpectedValueException(sprintf(
                "JSON 解析失败: %s",
                json_last_error_msg()
            ));
        }

        return $content;
    }
}

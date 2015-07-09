<?php

namespace Huying\Sms;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * 短信息
 *
 * Class Message
 */
class Message implements \ArrayAccess
{
    /**
     * 用于保存短信接收号码的数组
     *
     * @var string[]
     */
    protected $recipients;

    /**
     * 发送短信的号码
     *
     * 一般短信服务都不支持自定义发送号码，所以不需要理会此字段
     *
     * @var string
     */
    protected $from;

    /**
     * 短信内容
     *
     * @var string
     */
    protected $body;

    /**
     * 短信模板ID
     *
     * @var int|string
     */
    protected $templateId;

    /**
     * 短信模板填充内容
     *
     * @var string[]
     */
    protected $data = [];

    /**
     * 短信接口提供商
     *
     * $var ProviderInterface
     */
    protected $provider;

    /**
     * 服务商返回的此次接口调用ID
     *
     * @var int|string
     */
    protected $id;

    /**
     * 短信状态
     *
     * @var int
     */
    protected $status;

    /**
     * 解析过的接口回复结果
     *
     * @var mixed
     */
    protected $response;

    /**
     * 调用服务商接口的请求
     *
     * @var Request
     */
    protected $httpRequest;

    /**
     * 调用服务商接口返回的内容
     *
     * @var Response|\GuzzleHttp\Exception\GuzzleException
     */
    protected $httpResponse;

    /**
     * 调用服务商接口产生的错误
     *
     * @var ProviderException
     */
    protected $error;

    /**
     * 获取短信接收者列表
     *
     * @return string[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * 设置短信接收者
     *
     * @param string|string[] $recipients
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setRecipients($recipients)
    {
        $this->unsetRecipients();
        $this->addRecipient($recipients);

        return $this;
    }

    /**
     * 设置短信接收者
     *
     * @param string $recipient
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setRecipient($recipient)
    {
        return $this->setRecipients($recipient);
    }

    /**
     * 添加短信接收者
     *
     * @param string|string[] $recipients
     * @param bool $prepend 控制是否添加在头部，默认从尾部添加
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addRecipient($recipients, $prepend = false)
    {
        if (!$prepend) {
            $this->appendRecipient($recipients);
        } else {
            $this->prependRecipient($recipients);
        }

        return $this;
    }

    /**
     * 添加短信接收者到当前列表的尾部
     *
     * @param string|string[] $recipients
     * @throws \InvalidArgumentException
     */
    protected function appendRecipient($recipients)
    {
        $recipients = static::formatRecipient($recipients);
        $this->recipients = array_merge($this->recipients, $recipients);
    }

    /**
     * 添加短信接收者到当前列表的头部
     *
     * @param string|string[] $recipients
     * @throws \InvalidArgumentException
     */
    protected function prependRecipient($recipients)
    {
        $recipients = static::formatRecipient($recipients);
        $this->recipients = array_merge($recipients, $this->recipients);
    }

    /**
     * 转换接收者格式
     *
     * @param string|string[] $recipients
     * @return string[]
     * @throws \InvalidArgumentException
     */
    public static function formatRecipient($recipients)
    {
        if (is_string($recipients)) {
            $recipients = explode(',', trim($recipients, ", \t\n\r\0\x0B"));
        } elseif (!is_array($recipients)) {
            throw new \InvalidArgumentException('接受者必须为字符串或字符串数组');
        }

        return $recipients;
    }

    /**
     * 清空短信接收者列表
     */
    public function unsetRecipients()
    {
        $this->recipients = [];
    }

    /**
     * 获取发送者号码
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * 设置发送者号码
     *
     * @param string $from
     * @return self
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * 获取短信内容
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * 设置短信内容
     *
     * @param string $body
     * @return self
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * 获取短信模板ID
     *
     * @return int|string
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * 设置短信模板ID
     *
     * @param int|string $templateId
     * @return self
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * 获取用于填充模板的数据
     *
     * @return string[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 设置用于填充模板的数据
     *
     * @param string[] $data
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * 获取使用的短信供应商
     *
     * @return ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * 设置使用的短信供应商
     *
     * @param ProviderInterface $provider
     * @return self
     */
    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * 获取短信ID
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 设置短信ID
     *
     * @param int|string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * 获取短信状态
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 设置短信状态
     *
     * @param int $status
     */
    public function setStatus($status)
    {
        if (!in_array($status, MessageStatus::getValidStatus())) {
            throw new \InvalidArgumentException('短信状态非法');
        }
        $this->status = $status;
    }

    /**
     * 获取短信接口的回复
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * 设置短信接口的回复
     *
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * 获取短信接口的 HTTP 请求
     *
     * @return Request
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    /**
     * 设置短信接口的 HTTP 请求
     *
     * @param Request $httpRequest
     */
    public function setHttpRequest(Request $httpRequest)
    {
        $this->httpRequest = $httpRequest;
    }

    /**
     * 获取短信接口的 HTTP 回复
     *
     * @return Response|\GuzzleHttp\Exception\GuzzleException
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    /**
     * 设置短信接口的 HTTP 回复
     *
     * @param Response|\GuzzleHttp\Exception\GuzzleException $httpResponse
     */
    public function setHttpResponse($httpResponse)
    {
        $this->httpResponse = $httpResponse;
    }

    /**
     * 获取短信接口错误
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 设置短信接口错误
     *
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * 创建一条短信
     *
     * @param array $data
     * @return static
     */
    public static function create($data = [])
    {
        $message = new static();
        $message->fromArray($data);

        return $message;
    }

    /**
     * 初始化短信内容
     *
     * @param array $data
     * @return self
     */
    public function fromArray(array $data)
    {
        if (!empty($data['id'])) {
            $this->setId($data['id']);
        }

        if (isset($data['recipients'])) {
            $this->setRecipients($data['recipients']);
        }

        if (isset($data['recipient'])) {
            $this->addRecipient($data['recipient']);
        }

        if (isset($data['from'])) {
            $this->setFrom($data['from']);
        }

        if (isset($data['body'])) {
            $this->setBody($data['body']);
        }

        if (isset($data['data'])) {
            $this->setData($data['data']);
        }

        if (isset($data['template_id'])) {
            $this->setTemplateId($data['template_id']);
        }

        if (isset($data['status'])) {
            $this->setStatus($data['status']);
        } else {
            $this->setStatus(MessageStatus::STATUS_QUEUED);
        }

        return $this;
    }

    /**
     * 生成包含短信相关信息的数组
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'recipients' => $this->getRecipients(),
            'from' => $this->getFrom(),
            'body' => $this->getBody(),
            'data' => $this->getData(),
            'template_id' => $this->getTemplateId(),
            'status' => $this->getStatus(),
        ];
    }

    /**
     * 设置短信接口供应商
     *
     * 支持链式操作
     *
     * @param ProviderInterface $provider
     * @return $this
     */
    public function using(ProviderInterface $provider)
    {
        $this->setProvider($provider);

        return $this;
    }

    /**
     * 发送短信
     *
     * @return Message 返回发送状态改变后的短信
     * @throws \RuntimeException 未设置短信供应商
     */
    public function send()
    {
        $provider = $this->getProvider();
        if (!$provider) {
            throw new \RuntimeException('未设置短信供应商');
        }

        $provider->send($this);

        return $this;
    }

    /**
     * 检查一个偏移位置是否存在
     *
     * @param string $offset 需要检查的偏移位置
     * @return boolean 成功时返回 TRUE， 或者在失败时返回 FALSE
     */
    public function offsetExists($offset)
    {
        $method = 'get'.static::formatOffset($offset);

        return method_exists($this, $method) && null !== $this->{$method}();
    }

    /**
     * 获取一个偏移位置的值
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $method = 'get'.static::formatOffset($offset);

        return $this->offsetExists($offset) ? $this->{$method}() : null;
    }

    /**
     * 设置一个偏移位置的值
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            $method = 'set'.static::formatOffset($offset);
            $this->{$method}($value);
        }
    }

    /**
     * 复位一个偏移位置的值
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $method = 'set'.static::formatOffset($offset);
            $this->{$method}(null);
        }
    }

    /**
     * 转换偏移的格式
     *
     * @param string $offset
     * @return string
     */
    protected static function formatOffset($offset)
    {
        $offset = str_replace('_', '', $offset);

        return str_replace(' ', '', ucwords($offset));
    }
}

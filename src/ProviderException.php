<?php

namespace Huying\Sms;

/**
 * 短信接口异常类
 *
 * Class ProviderException
 */
class ProviderException extends \Exception
{
    /**
     * @var mixed
     */
    protected $response;

    /**
     * 构造函数
     *
     * @param string $message
     * @param int $code
     * @param array $response
     */
    public function __construct($message, $code, $response = [])
    {
        $this->response = $response;
        parent::__construct($message, $code);
    }

    /**
     * 获取短信接口返回的内容
     *
     * @return array|mixed
     */
    public function getResponseBody()
    {
        return $this->response;
    }
}

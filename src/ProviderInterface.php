<?php

namespace Huying\Sms;

/**
 * 短信供应商接口
 *
 * Interface ProviderInterface
 */
interface ProviderInterface
{
    /**
     * 获取短信供应商名称
     *
     * @return string
     */
    public function getName();

    /**
     * 发送短信
     *
     * @param Message $message
     * @return bool 发送结果，成功时返回 true ,失败时返回 true
     * @throws \InvalidArgumentException 短信参数不全时抛出
     */
    public function send(Message $message);

    /**
     * 返回供应商的优先级
     *
     * @return int
     */
    public function getPriority();

    /**
     * 设置供应商的优先级
     *
     * @param int $priority
     */
    public function setPriority($priority);
}

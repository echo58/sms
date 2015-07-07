<?php

namespace Huying\Sms;

/**
 * 短信状态类
 *
 * Class MessageStatus
 */
class MessageStatus
{
    /**
     * 消息未发送
     */
    const STATUS_QUEUED = 0;

    /**
     * 消息已发送，接口返回发送成功
     */
    const STATUS_SENT = 1;

    /**
     * 消息发送失败
     */
    const STATUS_FAILED = -1;

    /**
     * 返回有效的短信状态
     *
     * @return array
     */
    public static function getValidStatus()
    {
        return [
            self::STATUS_QUEUED,
            self::STATUS_SENT,
            self::STATUS_FAILED,
        ];
    }
}

<?php

namespace Huying\Sms\Test;

use Huying\Sms\MessageStatus;

class MessageStatusTest extends \PHPUnit_Framework_TestCase
{
    public function testClassConstantsExist()
    {
        $this->assertTrue(defined('Huying\Sms\MessageStatus::STATUS_QUEUED'));
        $this->assertTrue(defined('Huying\Sms\MessageStatus::STATUS_SENT'));
        $this->assertTrue(defined('Huying\Sms\MessageStatus::STATUS_FAILED'));
    }

    public function testGetValidStatus()
    {
        $validStatus = MessageStatus::getValidStatus();
        $this->assertTrue(is_array($validStatus));
    }
}

<?php

namespace Huying\Sms\Test;

use Huying\Sms\Message;
use Huying\Sms\MessageStatus;
use Huying\Sms\Pool;
use Huying\Sms\ProviderException;

class PoolTest extends \PHPUnit_Framework_TestCase
{
    public function testProviderSetterAndGetter()
    {
        $stubOne = $this->getMockBuilder('Huying\Sms\Test\FakeProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $stubOne->method('getName')
            ->willReturn('provider1');
        $stubOne->method('getPriority')
            ->willReturn('2');
        $stubTwo = $this->getMockBuilder('Huying\Sms\Test\FakeProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $stubTwo->method('getName')
            ->willReturn('provider2');
        $stubTwo->method('getPriority')
            ->willReturn('1');

        $pool = new Pool();

        $this->assertInstanceOf('Huying\Sms\Pool', $pool->registerProvider($stubOne));
        $this->assertEquals(['provider1' => $stubOne], $pool->getProviders());

        // 不能重复注册同名的供应商
        $this->assertInstanceOf('Huying\Sms\Pool', $pool->registerProvider($stubOne));
        $this->assertEquals(['provider1' => $stubOne], $pool->getProviders());

        $this->assertInstanceOf('Huying\Sms\Pool', $pool->registerProvider($stubTwo));
        $this->assertEquals(['provider2' => $stubTwo, 'provider1' => $stubOne], $pool->getProviders());

        $pool->unsetProviders();
        $this->assertEquals([], $pool->getProviders());

        $this->assertInstanceOf('Huying\Sms\Pool', $pool->registerProviders([
            $stubTwo,
            $stubOne,
        ]));
        $this->assertEquals(['provider2' => $stubTwo, 'provider1' => $stubOne], $pool->getProviders());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     * @expectedException \Exception
     */
    public function testWrongProvider()
    {
        Pool::create()->registerProvider(new \stdClass());
    }

    public function testMessageSetterAndGetter()
    {
        $message1 = Message::create();
        $message2 = Message::create();
        $pool = Pool::create();

        $this->assertEquals([], $pool->getMessages());

        $this->assertInstanceOf('Huying\Sms\Pool', $pool->setMessages([$message1, $message2]));
        $this->assertEquals([$message1, $message2], $pool->getMessages());

        $this->assertInstanceOf('Huying\Sms\Pool', $pool->addMessage($message2, true));
        $this->assertEquals([$message2, $message1, $message2], $pool->getMessages());

        $this->assertInstanceOf('Huying\Sms\Pool', $pool->addMessage($message1));
        $this->assertEquals([$message2, $message1, $message2, $message1], $pool->getMessages());

        $this->assertInstanceOf('Huying\Sms\Pool', $pool->setMessage($message1));
        $this->assertEquals([$message1], $pool->getMessages());

        $pool->unsetMessages();
        $this->assertEquals([], $pool->getMessages());

        $this->assertInstanceOf('Huying\Sms\Pool', $pool->addMessages([$message1, $message2]));
        $this->assertEquals([$message1, $message2], $pool->getMessages());

        $this->assertInstanceOf('Huying\Sms\Pool', $pool->addMessages([$message1, $message2], true));
        $this->assertEquals([$message1, $message2, $message1, $message2], $pool->getMessages());
    }

    public function testSendOneProviderAndOneMessage()
    {
        $providerStub = $this->getMockBuilder('Huying\Sms\Test\FakeProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $providerStub->method('send')
            ->willReturn(true);

        $messageStub = $this->getMockBuilder('Huying\Sms\Message')
            ->disableOriginalConstructor()
            ->getMock();
        $messageStub->method('using')
            ->willReturn($messageStub);
        $messageStub->method('send')
            ->willReturn(true);
        $messageStub->method('getStatus')
            ->willReturn(MessageStatus::STATUS_SENT);

        $pool = Pool::create([$providerStub]);
        $pool->addMessage($messageStub)->send();
        $this->assertEquals([], $pool->getErrors());
    }

    public function testSendOneProviderAndOneMessageWrong()
    {
        $providerStub = $this->getMockBuilder('Huying\Sms\Test\FakeProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $providerStub->method('send')
            ->willReturn(false);

        $messageStub = $this->getMockBuilder('Huying\Sms\Message')
            ->disableOriginalConstructor()
            ->getMock();
        $messageStub->method('using')
            ->willReturn($messageStub);
        $messageStub->method('send')
            ->willReturn(false);
        $messageStub->method('getStatus')
            ->willReturn(MessageStatus::STATUS_FAILED);
        $error = new ProviderException('test', 1, []);
        $messageStub->method('getError')
            ->willReturn($error);

        $pool = Pool::create([$providerStub]);
        $pool->addMessage($messageStub)->send();
        $this->assertEquals([$error], $pool->getErrors());
    }
}

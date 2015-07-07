<?php

namespace Huying\Sms\Test;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class AbstractProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIncompleteConstructOptions()
    {
        $provider = new FakeProvider([
            'accountSid' => '123',
        ]);
    }

    public function testPrioritySetterAndGetter()
    {
        $provider = new FakeProvider([
            'accountSid' => '123',
            'authToken' => '456',
        ]);
        $provider->setPriority(10);
        $this->assertEquals(10, $provider->getPriority());
    }

    public function testHttpClientSetterAndGetter()
    {
        $httpClient = new HttpClient();
        $provider = new FakeProvider([
            'accountSid' => '123',
            'authToken' => '456',
        ]);

        $provider->setHttpClient($httpClient);
        $this->assertEquals($httpClient, $provider->getHttpClient());
    }

    public function testSend()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'status' => 0,
            ])),
        ]);
        $handler = HandlerStack::create($mock);
        $httpClient = new HttpClient(['handler' => $handler]);
        $provider = new FakeProvider([
            'accountSid' => '123',
            'authToken' => '456',
        ]);
        $provider->setHttpClient($httpClient);
        $messageStub = $this->getMockBuilder('Huying\Sms\Message')
            ->disableOriginalConstructor()
            ->getMock();
        $messageStub->expects($this->once())
            ->method('setHttpRequest');

        $this->assertTrue($provider->send($messageStub));
    }

    public function testSendWrongStatus()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'status' => 1,
            ])),
        ]);
        $handler = HandlerStack::create($mock);
        $httpClient = new HttpClient(['handler' => $handler]);
        $provider = new FakeProvider([
            'accountSid' => '123',
            'authToken' => '456',
        ]);
        $provider->setHttpClient($httpClient);
        $messageStub = $this->getMockBuilder('Huying\Sms\Message')
            ->disableOriginalConstructor()
            ->getMock();

        $provider->send($messageStub);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testSendWrongFormat()
    {
        $mock = new MockHandler([
            new Response(200, [], '["wrong"'),
        ]);
        $handler = HandlerStack::create($mock);
        $httpClient = new HttpClient(['handler' => $handler]);
        $provider = new FakeProvider([
            'accountSid' => '123',
            'authToken' => '456',
        ]);
        $provider->setHttpClient($httpClient);
        $messageStub = $this->getMockBuilder('Huying\Sms\Message')
            ->disableOriginalConstructor()
            ->getMock();

        $provider->send($messageStub);
    }
}

<?php

namespace Huying\Sms\Test;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Huying\Sms\Message;
use Huying\Sms\MessageStatus;
use Huying\Sms\ProviderException;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testRecipientsSetterAndGetter()
    {
        $message = new Message();

        $this->assertInstanceOf('Huying\Sms\Message', $message->setRecipients('18800000001'));
        $this->assertEquals(['18800000001'], $message->getRecipients());

        $this->assertInstanceOf('Huying\Sms\Message', $message->setRecipients('18800000002,18800000003'));
        $this->assertEquals(['18800000002', '18800000003'], $message->getRecipients());

        $this->assertInstanceOf('Huying\Sms\Message', $message->setRecipients(",18800000004,18800000005 \n"));
        $this->assertEquals(['18800000004', '18800000005'], $message->getRecipients());

        $this->assertInstanceOf('Huying\Sms\Message', $message->setRecipients(['18800000006']));
        $this->assertEquals(['18800000006'], $message->getRecipients());

        $this->assertInstanceOf('Huying\Sms\Message', $message->setRecipients(['18800000007', '18800000008']));
        $this->assertEquals(['18800000007', '18800000008'], $message->getRecipients());

        $message->unsetRecipients();
        $this->assertEquals([], $message->getRecipients());

        $this->assertInstanceOf('Huying\Sms\Message', $message->addRecipient('18800000009'));
        $this->assertEquals(['18800000009'], $message->getRecipients());

        $this->assertInstanceOf('Huying\Sms\Message', $message->addRecipient(['18800000010']));
        $this->assertEquals(['18800000009', '18800000010'], $message->getRecipients());

        $this->assertInstanceOf('Huying\Sms\Message', $message->addRecipient(['18800000011', '18800000012']));
        $this->assertEquals(['18800000009', '18800000010', '18800000011', '18800000012'], $message->getRecipients());

        $this->assertInstanceOf('Huying\Sms\Message', $message->addRecipient('18800000013', true));
        $this->assertEquals(['18800000013', '18800000009', '18800000010', '18800000011', '18800000012'], $message->getRecipients());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongRecipients()
    {
        $message = new Message();
        $message->setRecipient(new \stdClass());
    }

    public function testOtherSettersAndGetters()
    {
        $message = new Message();

        $this->assertInstanceOf('Huying\Sms\Message', $message->setFrom('123456789'));
        $this->assertEquals('123456789', $message->getFrom());

        $this->assertInstanceOf('Huying\Sms\Message', $message->setBody('你好'));
        $this->assertEquals('你好', $message->getBody());

        $this->assertInstanceOf('Huying\Sms\Message', $message->setTemplateId('1'));
        $this->assertEquals('1', $message->getTemplateId());

        $this->assertInstanceOf('Huying\Sms\Message', $message->setData(['4523', 15]));
        $this->assertEquals(['4523', 15], $message->getData());

        $provider = new FakeProvider([
            'accountSid' => '123',
            'authToken' => '456'
        ]);
        $this->assertInstanceOf('Huying\Sms\Message', $message->setProvider($provider));
        $this->assertEquals($provider, $message->getProvider());

        $message->setId(123);
        $this->assertEquals(123, $message->getId());

        $message->setStatus(MessageStatus::STATUS_SENT);
        $this->assertEquals(MessageStatus::STATUS_SENT, $message->getStatus());

        $parsedResponse = [
            'status' => 0,
            'id' => 12312,
        ];
        $message->setResponse($parsedResponse);
        $this->assertEquals($parsedResponse, $message->getResponse());

        $httpRequest = new Request('POST', 'http://fake.url');
        $message->setHttpRequest($httpRequest);
        $this->assertEquals($httpRequest, $message->getHttpRequest());

        $httpResponse = new Response();
        $message->setHttpResponse($httpResponse);
        $this->assertEquals($httpResponse, $message->getHttpResponse());

        $error = new ProviderException('test', '2', ['123213', '456']);
        $message->setError($error);
        $this->assertEquals($error, $message->getError());
        $this->assertEquals(['123213', '456'], $message->getError()->getResponseBody());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongStatus()
    {
        Message::create()->setStatus(-1000);
    }

    public function testUsing()
    {
        $message = new Message();
        $provider = new FakeProvider([
            'accountSid' => '123',
            'authToken' => '456'
        ]);
        $obj = $message->using($provider);
        $this->assertEquals($provider, $message->getProvider());
        $this->assertInstanceOf('Huying\Sms\Message', $obj);
    }

    public function testCreateMessage()
    {
        $message = Message::create([
            'body' => 'test'
        ]);
        $this->assertInstanceOf('Huying\Sms\Message', $message);
    }

    public function testFromArrayAndToArray()
    {
        $message = new Message();
        $this->assertInstanceof('Huying\Sms\Message', $message->fromArray([
            'recipients' => '18800000001,18800000002',
            'recipient' => '18800000003',
            'from' => '123456',
            'body' => '短信内容',
            'template_id' => 111,
            'data' => [
                '1',
                '2',
            ]
        ]));
        $this->assertEquals([
            'id' => null,
            'recipients' => [
                '18800000001',
                '18800000002',
                '18800000003',
            ],
            'from' => '123456',
            'body' => '短信内容',
            'data' => [
                '1',
                '2',
            ],
            'template_id' => 111,
            'status' => MessageStatus::STATUS_QUEUED,
        ], $message->toArray());
    }

    public function testArrayAccess()
    {
        $message = new Message();
        $message->fromArray([
            'id' => 1,
            'recipients' => '18800000001,18800000002',
            'body' => '短信内容',
            'status' => MessageStatus::STATUS_SENT
        ]);

        $this->assertTrue(isset($message['id']));
        $this->assertTrue(!isset($message['from']));

        $this->assertEquals(1, $message['id']);

        $message['status'] = MessageStatus::STATUS_QUEUED;
        $this->assertEquals(MessageStatus::STATUS_QUEUED, $message['status']);

        unset($message['id']);
        $this->assertTrue(!isset($message['id']));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSendWithoutProvider()
    {
        Message::create()->setRecipient('18800000001')->setBody('你好')->send();
    }

    public function testSend()
    {
        $stub = $this->getMockBuilder('Huying\Sms\Test\FakeProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $stub->method('send')
            ->willReturn(true);

        $result = Message::create()->setRecipient('18800000001')->setBody('你好')->using($stub)->send();
        $this->assertInstanceOf('Huying\Sms\Message', $result);
    }
}

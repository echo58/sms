<?php

namespace Huying\Sms\Test;

use GuzzleHttp\Exception\GuzzleException;
use Huying\Sms\AbstractProvider;
use Huying\Sms\Message;
use Huying\Sms\ProviderException;

class FakeProvider extends AbstractProvider
{
    protected $accountSid;
    protected $authToken;

    protected function getRequiredOptions($key)
    {
        if ($key == self::PROVIDER_OPTIONS) {
            return ['accountSid', 'authToken'];
        } elseif ($key == self::MESSAGE_OPTIONS) {
            return ['recipients', 'content'];
        } else {
            return [];
        }
    }

    protected function getUrl(Message $message)
    {
        return 'http://fake.url/accountSid/'.$this->accountSid;
    }

    protected function getRequestMethod()
    {
        return self::METHOD_POST;
    }

    protected function getRequestHeaders()
    {
        return [
            'Authorization' => $this->authToken
        ];
    }

    protected function getRequestPayload(Message $message)
    {
        return 'test';
    }

    protected function handleResponse($response)
    {
        if ($response instanceof GuzzleException) {
            throw new ProviderException('test', 2, []);
        }

        $jsonParsed = static::parseJson($response->getBody());
        if (isset($jsonParsed['status']) and $jsonParsed['status'] == 0) {
            return $jsonParsed;
        } else {
            throw new ProviderException('test', 2, $jsonParsed);
        }
    }

    public function getName()
    {
        return 'fake provider';
    }
}

# 短信发送接口包

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

本包对常见的短信发送功能进行了抽象，包含了短信发送这一功能实现过程中需要用到类，使得具体实现某一短信平台的短信发送功能异常简单。

## 安装

### 短信包开发者

通过 Composer 安装

``` bash
$ composer require huying/sms
```

### 短信包使用者

直接安装对应短信平台的包即可，不需要直接安装本包，目前支持的短信平台如下：
- 容联云通讯
- 云片网络

## 使用方法

### 实例化短信平台类

```php
$provider = new Huying\Sms\<ProvioderName>([
    'accountSid' => 'xxxxx',
    'authToken' => 'xxxxx',
    'appId' => 'xxxxxx',
]);
```
实例化时需要传给构造函数的参数在不同短信平台下一般是不一样的，具体请见相应平台的包。

### 直接发送短信

```php
$message = Message::create()
    ->setRecipient('18800000000')
    ->setBody('我是短信内容')
    ->using($provider)
    ->send();
```

### 判断短信是否发送成功

```php
if ($message->getStatus() == Huying\Sms\MessageStatus::STATUS_SENT) {
    echo '发送成功';
} else {
    echo '发送失败:错误码'.$message->getError()->getCode()
        .',错误消息:'.$message->getError()->getMessage();
}
```

### 短信池

短信池中可以放入多条短信，然后一次发送
```php
$pool = Pool::create([$provider]);
$pool->addMessage($message1);
$pool->addMessage($message2);
$pool->send();
```
短信池支持设置多个平台的provider，发送短信池中短信时，会按照provider的优先级逐一尝试，直到发送成功为止
```php
$pool = Pool::create([$provider1, $provider2]);
```

## 更新日志

请访问 [更新日志](CHANGELOG.md) 查看有关该项目的更新信息。

## 贡献代码

请查看 [贡献指南](CONTRIBUTING.md)。

## 开发者

- [Xujian Chen][link-author]
- [所有贡献者][link-contributors]

## 许可协议

本项目使用 MIT 协议，详情请查看 [License File](LICENSE.md)。

[ico-version]: https://img.shields.io/packagist/v/huying/sms.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/echo58/sms/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/echo58/sms.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/echo58/sms.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/huying/sms.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/huying/sms
[link-travis]: https://travis-ci.org/echo58/sms
[link-scrutinizer]: https://scrutinizer-ci.com/g/echo58/sms/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/echo58/sms
[link-downloads]: https://packagist.org/packages/huying/sms
[link-author]: https://github.com/:author_username
[link-contributors]: ../../contributors

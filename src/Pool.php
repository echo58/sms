<?php

namespace Huying\Sms;

/**
 * 短信池
 *
 * 用于保存所有需要发送的短信，然后一次发送
 *
 * Class Pool
 */
class Pool
{
    /**
     * 短信接口供应商列表
     *
     * @var ProviderInterface[]
     */
    protected $providers = [];

    /**
     * 需要发送的短信列表
     *
     * @var Message[]
     */
    protected $messages = [];

    /**
     * @var ProviderException[]
     */
    protected $errors = [];

    /**
     * 创建一个空短信池
     *
     * @param array $providers
     * @return static
     */
    public static function create($providers = [])
    {
        $pool = new static($providers);

        return $pool;
    }

    /**
     * 构造函数
     *
     * @param array $providers
     */
    public function __construct($providers = [])
    {
        $this->registerProviders($providers);
    }

    /**
     * 获取短信接口供应商列表
     *
     * @return ProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * 添加一个短信接口供应商
     *
     * @param ProviderInterface $provider
     * @return $this
     */
    public function registerProvider(ProviderInterface $provider)
    {
        $this->providers[$provider->getName()] = $provider;
        $this->sortProviders();

        return $this;
    }

    /**
     * 在现有短信接口供应商列表中添加一批新的短信接口供应商
     *
     * @param ProviderInterface[] $providers
     * @return $this
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            $this->providers[$provider->getName()] = $provider;
        }
        $this->sortProviders();

        return $this;
    }

    /**
     * 根据短信接口供应商的优先级对短信接口供应商列表进行排序
     *
     * 优先级数字越小，越靠前
     */
    protected function sortProviders()
    {
        // fix: uasort(): Array was modified by the user comparison function
        $simpleProviders = [];
        foreach ($this->providers as $name => $provider) {
            $simpleProviders[$name] = $provider->getPriority();
        }
        asort($simpleProviders);
        $providers = [];
        foreach ($simpleProviders as $name => $priority) {
            $providers[$name] = $this->providers[$name];
        }
        $this->providers = $providers;
    }

    /**
     * 清空短信接口供应商列表
     */
    public function unsetProviders()
    {
        $this->providers = [];
    }

    /**
     * 返回需要发送的短信列表
     *
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * 设置需要发送的消息列表
     *
     * @param Message[] $messages
     * @return $this
     */
    public function setMessages(array $messages)
    {
        $this->unsetMessages();
        foreach ($messages as $message) {
            $this->appendMessage($message);
        }

        return $this;
    }

    /**
     * 设置需要发送的消息
     *
     * @param Message $message
     * @return $this
     */
    public function setMessage(Message $message)
    {
        $this->messages = [$message];

        return $this;
    }

    /**
     * 添加一条需要发送的消息
     *
     * @param Message $message
     * @param bool $prepend 控制添加的消息是添加在消息队列头部还是尾部，默认添加在尾部
     * @return $this
     */
    public function addMessage(Message $message, $prepend = false)
    {
        if (!$prepend) {
            $this->appendMessage($message);
        } else {
            $this->prependMessage($message);
        }

        return $this;
    }

    /**
     * 添加一批需要发送的消息
     *
     * @param Message[] $messages
     * @param bool $prepend 控制添加的消息是添加在消息队列头部还是尾部，默认添加在尾部
     * @return $this
     */
    public function addMessages(array $messages, $prepend = false)
    {
        if ($prepend) {
            $messages = array_reverse($messages);
        }

        foreach ($messages as $message) {
            $this->addMessage($message, $prepend);
        }

        return $this;
    }

    /**
     * 在现有需要发送的短信列表头部添加一条新的短信
     *
     * @param Message $message
     */
    protected function prependMessage(Message $message)
    {
        array_unshift($this->messages, $message);
    }

    /**
     * 在现有需要发送的短信列表尾部添加一条新的短信
     *
     * @param Message $message
     */
    protected function appendMessage(Message $message)
    {
        $this->messages[] = $message;
    }

    /**
     * 清空需要发送的短信列表
     */
    public function unsetMessages()
    {
        $this->messages = [];
    }

    /**
     * 保存调用短信接口时产生的异常
     *
     * @param ProviderException $exception
     */
    protected function addError(ProviderException $exception)
    {
        $this->errors[] = $exception;
    }

    /**
     * 返回调用短信接口过程中产生的异常
     *
     * @return ProviderException[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * 依次发送当前短信列表中的短信
     *
     * @return Message[]
     */
    public function send()
    {
        $providers = $this->providers;
        $messages = $this->messages;
        $this->unsetMessages();
        foreach ($messages as &$message) {
            foreach ($providers as $provider) {
                $message->using($provider)->send();
                if ($message->getStatus() == MessageStatus::STATUS_SENT) {
                    break;
                } else {
                    $this->addError($message->getError());
                }
            }
        }

        return $messages;
    }
}

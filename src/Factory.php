<?php

namespace Hinet\Sms;

use Hinet\Sms\Storage\Storager;

class Factory
{
    //静态KEY
    const STATE_KEY = '_state';
    const CAN_RESEND_UNTIL_KEY = '_can_resend_until';
    protected $app;
    protected $config;
    //存储器
    protected static $storage;
    protected $token = null;
    //发送状态
    protected $state = [];
    //存储器驱动
    protected static $driver;

    public function __construct($config,$token)
    {
        $this->config = $config;
        self::$driver = $this->config['storage']['driver'];
        if ($token && is_string($token)) {
            $this->token = $token;
        }
        $this->reset();
    }

    /**
     * 重置发送状态
     */
    protected function reset()
    {
        $this->state = [
            'send'       => false,
            'to'         => null,
            'verifycode' => null,
            'deadline'   => 0,
            'attempts'   => $this->config['attempts'],
        ];
    }

    /**
     * 验证是否可发送
     *
     * @return array
     */
    public function validateSendable()
    {
        $time = $this->getCanResendTime();
        if ($time <= time()) {
            return true;
        } else {
            return false;
        }
    }

    public function setState($state = array())
    {
        $this->state = $state;
    }

    protected function generateKey()
    {
        $split  = '.';
        $prefix = config($this->config['storage']['prefix'], 'laravel_sms');
        $args   = func_get_args();
        array_unshift($args, $this->token);
        $args = array_filter($args, function ($value) {
            return $value && is_string($value);
        });
        if (!(empty($args))) {
            $prefix .= $split . implode($split, $args);
        }
        return $prefix;
    }

    /**
     * 获取存储器
     *
     * @return Storage
     * @throws LaravelSmsException
     *
     */
    protected static function storage()
    {
        if (self::$storage) {
            return self::$storage;
        }
        $className = (self::$driver == 'session') ? 'Hinet\Sms\Storage\SessionStorage' : 'Hinet\Sms\Storage\CacheStorage';
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Generate storage failed, the class $className does not exists.");
        }
        $store = new $className();//Hinet\Sms\Storage\CacheStorage        
        if (!($store instanceof Storager)) {
            throw new \InvalidArgumentException("Generate storage failed, the class $className does not implement the interface [Hinet\\Sms\\Storage\\Storager].");
        }
        return self::$storage = $store;
    }

    /**
     * 存储发送状态
     */
    public function storeState()
    {
        $this->updateState($this->state);
    }

    /**
     * 更新发送状态
     *
     * @param string|array $name
     * @param mixed $value
     */
    public function updateState($name, $value = null)
    {
        $state = $this->retrieveState();
        if (is_array($name)) {
            $state = array_merge($state, $name);
        } elseif (is_string($name)) {
            $state[$name] = $value;
        }
        $key = $this->generateKey(self::STATE_KEY);
        self::storage()->set($key, $state);
    }

    /**
     * 从存储器中获取发送状态
     *
     * @param string|null $name
     *
     * @return array
     */
    public function retrieveState($name = null)
    {
        $key   = $this->generateKey(self::STATE_KEY);
        $state = self::storage()->get($key, []);
        if ($name !== null) {
            return isset($state[$name]) ? $state[$name] : null;
        }
        return $state;
    }

    /**
     * 从存储器中删除发送状态
     */
    public function forgetState()
    {
        $key = $this->generateKey(self::STATE_KEY);
        self::storage()->forget($key);
    }

    /**
     * 设置多少秒后才能再次请求
     *
     * @param int $interval
     *
     * @return int
     */
    public function setCanResendAfter($interval)
    {
        $key  = $this->generateKey(self::CAN_RESEND_UNTIL_KEY);
        $time = time() + intval($interval);
        self::storage()->set($key, $time);
    }

    /**
     * 从存储器中获取可再次发送的截止时间
     *
     * @return int
     */
    public function getCanResendTime()
    {
        $key = $this->generateKey(self::CAN_RESEND_UNTIL_KEY);
        return (int)self::storage()->get($key, 0);
    }

}

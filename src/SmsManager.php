<?php

namespace Hinet\Sms;

use Hinet\Sms\Gateways\Gateway;
use InvalidArgumentException;

class SmsManager
{
    protected $app;

    /**
     * @param SmsGatewayInterface $gateway
     */
    function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Instantiated SMS Gateway
     * @param null $name
     * @return mixed
     */
    public function gateway($name = null)
    {
        $factories = $this->getFactories();
        if ($name == 'fallback') {
            $name = $this->getFallbackGateway();
        } else {
            $name = $name ? $name : $this->getDefaultGateway();
        }
        if (!array_key_exists($name, $factories)) {
            throw new InvalidArgumentException("Gateway '$name' is not supported.");
        }
        $className = $factories[$name];
        $config    = $this->getConfig($name);
        $token    = $this->getToken();
        return new $className($config,$token);
    }

    protected function getConfig($name)
    {
        $config = $this->array_remove('gateways', $this->app['config']['sms']);
        return array_merge($config, $this->app['config']["sms.gateways.{$name}"]);
    }

    protected function getToken(){
        if($this->app->request->ajax()){
            $token = $this->app->request->header('X-CSRF-TOKEN') ?? $this->app->request->cookie('XSRF-TOKEN');
        }else{
            $token = $this->app->request->input('_token');
        }
        return $token;
    }

    protected function getDefaultGateway()
    {
        return $this->app['config']['sms.default'];
    }

    protected function getFallbackGateway()
    {
        return $this->app['config']['sms.fallback'];
    }

    protected function array_remove($key, $array)
    {
        if (!array_key_exists($key, $array)) {
            return $array;
        }
        $keys  = array_keys($array);
        $index = array_search($key, $keys);
        if ($index !== FALSE) {
            array_splice($array, $index, 1);
        }
        return $array;
    }

    /**
     * Get gateway maps
     * @return array
     */
    private function getFactories()
    {
        $agents    = $this->app['config']['sms.gateways'];
        $factories = [];
        foreach ($agents as $key => $value) {
            $factories[$key] = __NAMESPACE__ . '\Gateways\\' . ucfirst(strtolower($key)) . 'Gateway';
        }
        return $factories;
    }

}

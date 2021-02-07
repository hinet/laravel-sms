<?php

namespace Hinet\Sms\Gateways;

use Hinet\Sms\Contracts\Repository;
use Hinet\Sms\Factory;

class Gateway implements Repository
{
    protected $config = [];
    private $storage;
    //验证码
    protected $code;
    //模版ID
    protected $templateId;
    //短信内容
    protected $content;

    function __construct($config,$token)
    {
        $this->config  = $config;
        $this->storage = new Factory($config,$token);
    }

    public function send($mobile, $content = '')
    {
        $this->setVerifyCode($mobile);
        $url    = 'http://';
        $params = [
            'mobile'  => $mobile,
            'content' => empty($content) ? $this->content : $content
        ];
        return $this->curl($url, $mobile, $params);
    }

    public function response($response)
    {
        if ($response) {
            $result     = json_decode($response, true);
            $error_code = $result['error_code'];
            if ($error_code == 0) {
                //状态为0，说明短信发送成功
                return json_encode(['status' => 1, 'message' => '短信发送成功,短信ID：' . $result['result']['sid']]);
            } else {
                //状态非0，说明失败
                $message = $result['reason'];
                return json_encode(['status' => 0, 'message' => '短信发送失败,错误码：' . $error_code . ',错误信息：' . $message]);
            }
        } else {
            return json_encode(['status' => 0, 'message' => 'Http请求错误']);
        }
    }

    public function verifyCode($mobile, $value = null)
    {
        $state = $this->storage->retrieveState();
        if (isset($state['attempts'])) {
            $maxAttempts = $this->config['attempts'];
            $attempts    = $state['attempts'] + 1;
            $this->storage->updateState('attempts', $attempts);
            if ($maxAttempts > 0 && $attempts > $maxAttempts) {
                return false;
            }
        }
        return $state && $state['deadline'] >= time() && $state['to'] == $mobile && $state['verifycode'] == intval($value);
    }

    public function setVerifyCode($phone = '', $type = '')
    {
        $this->code = $this->makeRandom();
        //存储验证码
        $this->storage->setState([
            'send'       => true,
            'to'         => $phone,
            'verifycode' => $this->code,
            'deadline'   => strtotime("+".$this->config['minutes']." minute"),
            'attempts'   => 0,
        ]);
        if (!$this->storage->validateSendable()) {
            return json_encode(['status' => 0, 'message' => '发送过于频繁']);
        }
        $this->storage->storeState();
        $this->storage->setCanResendAfter((int)$this->config['interval']);
        if (empty($this->content)) {
            $this->content = $this->getTemplateContent($type);
        }
        $this->content = str_replace('{verifycode}', $this->code, $this->content);
        $this->content = str_replace('{minutes}', $this->config['minutes'], $this->content);

    }

    public function curl($url, $params = array(), $method = 'GET', $headers = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if (strtoupper($method) == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                $params = http_build_query($params);
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            throw new \InvalidArgumentException("cURL Error:" . curl_error($ch));
        }
        curl_close($ch);
        return $this->response($response);
    }

    public function getTemplateContent($type = '')
    {
        if (empty($type)) {
            return $this->config['template']['content'];
        } else {
            return $this->config['template'][$type]['content'];
        }
    }

    public function getTemplateId($type = '')
    {
        if (empty($type)) {
            return $this->config['template']['templateid'];
        } else {
            return $this->config['template'][$type]['templateid'];
        }
    }

    protected function makeRandom()
    {
        return random_int(100000, 999999);
    }
}

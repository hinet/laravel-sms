<?php
namespace Hinet\Sms\Contracts;

interface Repository
{
    /**
     * send sms method
     * @param $mobile
     * @param $content
     * @return mixed
     */
    public function send($mobile,$content);
    public function verifyCode($mobile,$value = null);
    public function setVerifyCode($time = null);
    public function getTemplateContent();
    public function getTemplateId();
    /**
     * @param $url
     * @param array $params
     * @return mixed
     */
    public function curl($url,$params = array(),$method);
    /**
     * @param $response
     * @return mixed
     */
    public function response($response);
}
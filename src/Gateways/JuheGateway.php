<?php
namespace Hinet\Sms\Gateways;
class JuheGateway extends Gateway{
    protected $headers = [];

    public function send($mobile, $content = '', $template = 'register')
    {
        $this->setVerifyCode($mobile);
        $url    = 'http://v.juhe.cn/vercodesms/send.php';
        $params = [
            'mobile'  => $mobile,
            'tplId' => $this->getTemplateId($template),
            'tplValue' => urlencode("#code#={$this->code}"),
            'key' => $this->config['app_key'],//你申请的APP_KEY
        ];
        if (is_array($content)) {
            $params = array_merge($content, $params);
        }
        return $this->curl($url, $params, 'GET', $this->headers);
    }

    public function response($response)
    {
        if ($response) {
            $result = json_decode($response, true);
            if ($result['error_code'] === 0) {
                return json_encode(['status' => 1, 'message' => '短信发送成功']);
            } else {
                return json_encode(['status' => 0, 'message' => '短信发送失败：' . $result["reason"] . ',错误码：' . $result['error_code']]);
            }
        } else {
            return json_encode(['status' => 0, 'message' => 'Http请求错误']);
        }
    }
}

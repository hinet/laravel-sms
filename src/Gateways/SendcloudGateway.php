<?php
/**
 * Created by PhpStorm.
 * User: honfei
 * Date: 2017/4/26
 * Time: 13:24
 */

namespace Hinet\Sms\Gateways;

class SendcloudGateway extends Gateway
{
    protected $headers;

    public function send($mobile, $content = '')
    {
        $this->setVerifyCode($mobile);
        $url    = 'https://api.sendcloud.net/smsapi/send';
        $params = [
            'smsUser'     => $this->config['SMS_USER'],//你申请的签名ID
            'templateId' => $this->getTemplateId(),
            'phone'  => $mobile,
            'msgType' => 0, //0表示短信, 1表示彩信,2表示国际短信,3表示国内语音,5表示影音 默认值为0
            'vars'   => json_encode(array(
                "code" => $this->code,  //模板里面的key变量  ${key}
            ), 1),
        ];
        if (is_array($content)) {
            $params = array_merge($content, $params);
        }
        $params['signature'] = $this->genSign($this->config['SMS_KEY'],$params);
        $this->headers = array(
            'Content-Type:application/x-www-form-urlencoded',
        );
        return $this->curl($url, $params, 'GET',$this->headers);
    }

    public function response($response)
    {
        if ($response) {
            $result = json_decode($response, true);
            $message = [];
            switch($result['statusCode']){
                case 200:
                    $message = ['status' => 1, 'message' => '短信发送成功'];
                    break;
                case 311:
                    $message = ['status' => 0, 'message' => '部分号码请求成功'];
                    break;
                case 312:
                    $message = ['status' => 0, 'message' => '全部请求失败'];
                    break;
                case 401:
                    $message = ['status' => 0, 'message' => '短信内容不能为空'];
                    break;
                case 402:
                    $message = ['status' => 0, 'message' => '短信内容不能超过%s个字符'];
                    break;
                default:
                    $message = ['status' => 0, 'message' => '短信发送失败：' . $result['message'] . ',错误码：' . $result['statusCode']];
                break;
            }
            return json_encode($message);
        } else {
            return json_encode(['status' => 0, 'message' => 'Http请求错误']);
        }
    }
    protected function genSign(string $apiKey,$params)
    {
        ksort($params);
        $signParts = [ $apiKey, $apiKey ];
        foreach ($params as $key => $value) {
            array_splice($signParts, -1, 0, $key.'='.$value);
        }
        return md5(join('&', $signParts));
    }
}

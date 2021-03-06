<?php
/**
 * Created by PhpStorm.
 * User: honfei
 * Date: 2017/4/26
 * Time: 13:24
 */

namespace Hinet\Sms\Gateways;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class AliyunGateway extends Gateway
{
    protected $headers;

    public function send($mobile, $parameters = '', $template = 'register')
    {
        $this->setVerifyCode($mobile, $template);
        //实例化SDK
        AlibabaCloud::accessKeyClient($this->config['app_key'], $this->config['app_secret'])
            ->regionId($this->config['end_point']) // replace regionId as you need
            ->asDefaultClient();
        try {

            $params = [
                'code' => $this->code
            ];
            if (is_array($parameters)) {
                $params = array_merge($parameters, $params);
            }


            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId'        => $this->config['end_point'],
                        'PhoneNumbers'    => $mobile,
                        'SignName'        => isset($this->config['template'][$template]['sign_name']) ? $this->config['template'][$template]['sign_name'] : $this->config['sign_name'],
                        'TemplateCode'    => $this->getTemplateId($template),
                        'TemplateParam'   => json_encode($params),
                        'SmsUpExtendCode' => $this->config['sms_up_extend_code'],
                    ],
                ])
                ->request();
            return $this->response($result->toArray());
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
        return $this->response();
    }

    public function response($response = '')
    {
        if (is_array($response)) {
            if ($response['Code'] == 'OK') {
                return json_encode(['status' => 1, 'message' => '短信发送成功']);
            } else {
                return json_encode(['status' => 0, 'message' => $response['Message'] ,'code' => $response['Code']]);
            }
        } else {
            return json_encode(['status' => 0, 'message' => 'Http请求错误']);
        }
    }
}

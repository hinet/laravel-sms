<?php
/**
 * Created by PhpStorm.
 * User: honfei
 * Date: 2017/4/26
 * Time: 13:24
 */

namespace Hinet\Sms\Gateways;

class QcloudGateway extends Gateway
{
    public function send($mobile, $content='')
    {
        $this->setVerifyCode($mobile);
        $url = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms';
        $params = [
            'type'    => 0,//0:普通短信;1:营销短信
            'msg'    => empty($content) ? $this->content : $content,
            'tel'   => ["nationcode"=> "86","mobile"=>$mobile],
            'time' => time(),
            'extend' => "",
            'ext' => "",
        ];

        $params['sig'] = $this->genSign($params);
        return $this->curl($url.'?'.'sdkappid='.$this->config['app_id'].'&random='.$this->code,json_encode($params),'POST');
    }
    public function response($response)
    {
        if($response)
        {
            $result = json_decode($response, true);
            if($result['result'] == 0)
            {
                return json_encode(['status'=>1,'message'=>'短信发送成功']);
            }else{
                return json_encode(['status'=>0,'message'=>'短信发送失败,错误码：'.$result['result']]);
            }
        }else{
            return json_encode(['status'=>0,'message'=>'Http请求错误']);
        }
    }
    protected function genSign($params)
    {
        $phone = $params['tel']["mobile"];
        $signature = "appkey=".$this->config['app_key']."&random=".$this->code."&time=".$params['time']."&mobile=".$phone;
        return hash("sha256",$signature, FALSE);
    }
}

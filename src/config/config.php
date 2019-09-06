<?php
return[
    'debug' => false,
    //默认短信网关
    'default' => env('SMS_GATEWAY_DEFAULT','qcloud'),
    //间隔时间(秒)
    'interval' => 60,
    //尝试次数,超过该数值验证码自动失效，0或负数则不启用
    'attempts'   => 0,
    //验证码失效时间(分钟)
    'minutes' => 5,
    'fallback' => '',
    //短信网关
    'gateways' => [
        'juhe' => [
            'app_key' => '',
            'app_secret' => '',
            'template' => [
                'templateid' => '',
                'content' => '',
            ]
        ],
        'qcloud' =>[
            'app_id' => 'sdkappid请填写您在腾讯云上申请到的',
            'app_key' => 'sdkappid对应的appkey，需要业务方高度保密',
            'template' => [
                'content' => '您的验证码是{verifycode}，有效期为{minutes}分钟，请尽快验证。'
            ]
        ],
        'aliyun' =>[
            'app_key' => '',
            'app_secret' => '',
            'end_point' => 'cn-hangzhou',
            //短信签名名称
            'sign_name' => '',
            //上行短信扩展码，无特殊需要此字段的用户请忽略此字段。
            'sms_up_extend_code' => '',
            'template' => [
                'register'=>[
                    'templateid' => '',
                    'content' => ''
                ],
                'updatePassword'=>[
                    'templateid' => '',
                    'content' => ''
                ],
                'updatePhone'=>[
                    'templateid' => '',
                    'content' => ''
                ]

            ]
        ],
        'baidu' =>[
            'endPoint' => 'sms.bj.baidubce.com',//SMS服务域名
            'accessKey' => '百度云安全认证Access Key',
            'secretAccessKey' => '百度云安全认证Secret Key',
            'invokeId'=>'签名调用ID',
            'template' => [
                'templateid' => '模板ID',
                'content' => '您的验证码是${code}，如非本人操作，请忽略本短信'
            ]
        ],
    ],
    //存储器
    'storage' => [
        'prefix' => '',//存储key的前缀
        'driver' => 'session',//存储方式,内置可选的值有'session'和'cache',api路由请使用cache驱动
    ]
];

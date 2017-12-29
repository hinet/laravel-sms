# laravel-sms

基于Laravel5的短信发送网关，易于扩展，你可以自由定义自已的网关驱动；目前支持腾讯云、百度云等短信API。

## 安装
composer require hinet/laravel-sms

## 配置
* 注册服务提供器
在config/app.php文件中providers数组里加入：
```php
Hinet\Sms\SmsServiceProvider::class,
```
在config/app.php文件中的aliases数组里加入
```php
'Sms' => Hinet\Sms\Facades\Sms::class,
```

* 发布配置文件
```php
php artisan vendor:publish --tag=smsconfig
```
* 注意事项

在web路由中，可以使用session或cache来存储状态；在api路由需要使用cache存储，因为api中间件中不含session会话支持
```php
//config/sms.php
//存储器
'storage' => [
        'prefix' => '',//存储key的前缀
        'driver' => 'cache',//存储方式,内置可选的值有'session'和'cache',api路由中请使用cache
]
```
## 使用方法

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sms;

class HomeController extends Controller
{
    public function index(){
    	$gateway = Sms::gateway('qcloud');
    	//发送
    	echo $gateway->send('测试手机号码');
    	//校验码是否正确
    	echo $gateway->verifyCode('测试手机号码','验证码');
    }
}
```

## 自定义验证

打开app\Providers\ValidatorServiceProvider.php文件，在boot()方法中添加：
```php
Validator::extend('verify_sms_code', function ($attribute, $value, $parameters) {
	$mobile = app('request')->input($parameters[0]);
	$gateway = \Sms::gateway(config('sms.default'));
	return $gateway->verifyCode($mobile,$value);
});
```
验证示例：
```php
$validator = Validator::make($data, [
      'phone' => 'unique:表名',
      'verifyCode' => 'verify_sms_code:phone',//phone为表单中的手机号字段名
]);
```

## Testing
拷贝单元测试文件SmsUnitTest.php到根目录tests文件中',并在命令行执行:
```php
vendor\bin\phpunit tests\SmsUnitTest.php
```

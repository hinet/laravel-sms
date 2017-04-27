# laravel-sms

基于Laravel5的短信发送网关，极易扩展，你可以自由定义自已的网关驱动。

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



## Testing
拷贝单元测试文件SmsUnitTest.php到根目录tests文件中',并在命令行执行:
```php
vendor\bin\phpunit tests\SmsUnitTest.php
```

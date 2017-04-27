<?php
namespace Hinet\Sms;
class SmsUnitTest extends \TestCase
{
    private $mobile = '13837193800';//测试手机号码'
    public function setUp()
    {
        parent::setUp();
        $this->app = $this->createApplication();
    }
    public function manager()
    {
        return new SmsManager($this->app);
    }
    public function testGateway()
    {
        $manager = $this->manager();
        $gateway = $manager->gateway('qcloud');
        $isObject = is_object($gateway);
        $this->assertTrue($isObject);
    }
    public function testQcloudGateway(){
        $manager = $this->manager();
        $gateway = $manager->gateway('qcloud');
        $result = $gateway->send($this->mobile);
        //$result = $gateway->verifyCode('12346');
        echo $result;
    }
    // public function testVerifyCode(){
    //     $manager = $this->manager();
    //     $gateway = $manager->gateway('qcloud');
    //     $result = $gateway->verifyCode('13837193800','12346');
    //     echo $result;
    // }
}
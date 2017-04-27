<?php
/**
 * 实现Session存储接口.
 * User: hinet
 * Date: 2016/11/2
 * Time: 9:32
 */

namespace Hinet\Sms;


class SessionStorage implements Storage
{
    public function set($key, $value)
    {
        session([
            $key => $value,
        ]);
    }
    public function get($key, $default)
    {
        return session($key, $default);
    }
    public function forget($key)
    {
        session()->forget($key);
    }
}
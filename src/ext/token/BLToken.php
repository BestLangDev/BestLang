<?php

namespace BestLang\ext\token;

use BestLang\core\BLConfig;

abstract class BLToken
{
    abstract function signInternal($payload, $expire, $options = []);

    abstract function unsignInternal($token);

    private static $_handle;

    private static function checkInit()
    {
        if (!isset(self::$_handle)) {
            self::$_handle = self::getHandle();
            if (!isset(self::$_handle)) {
                throw new \Exception('No token providers found');
            }
        }
    }

    private static function getHandle()
    {
        $configProvider = BLConfig::get('token', 'provider');
        if (!empty($configProvider)) {
            try {
                $handle = (new \ReflectionClass($configProvider))->newInstance();
                return $handle;
            } catch (\Exception $e) {
            }
        }
        return null;
    }

    public static function sign($payload, $expire, $options = [])
    {
        self::checkInit();
        return self::$_handle->signInternal($payload, $expire, $options);
    }

    public static function unsign($token)
    {
        self::checkInit();
        return self::$_handle->unsignInternal($token);
    }
}
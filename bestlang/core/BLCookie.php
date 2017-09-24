<?php

namespace bestlang\core;


class BLCookie
{
    public static function get($key = null, $default = null)
    {
        if (is_null($key)) {
            // get all
            return $_COOKIE;
        }
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    public static function has($key)
    {
        return !is_null(self::get($key));
    }

    public static function set($key, $value = '', $expire = 0)
    {
        if (is_array($key)) {
            foreach ($key as $realKey => $realValue) {
                setcookie($realKey, $realValue, $value);
                $_COOKIE[$realKey] = $realValue;
            }
        } else {
            setcookie($key, $value, $expire);
            $_COOKIE[$key] = $value;
        }
    }

    public static function delete($key)
    {
        if (is_array($key)) {
            foreach ($key as $realKey) {
                setcookie($realKey, '', $_SERVER['REQUEST_TIME'] - 3600);
                unset($_COOKIE[$realKey]);
            }
        } else {
            setcookie($key, '', $_SERVER['REQUEST_TIME'] - 3600);
            unset($_COOKIE[$key]);
        }
    }
}
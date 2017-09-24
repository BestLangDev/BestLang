<?php

namespace bestlang\core;


class BLSession
{
    private static $init = false;

    private static function checkInit()
    {
        if (!self::$init) {
            if (session_status() != PHP_SESSION_ACTIVE) {
                session_start();
            }
            self::$init = true;
        }
    }

    public static function get($key = null, $default = null)
    {
        self::checkInit();

        if (is_null($key)) {
            // get all
            return $_SESSION;
        }
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function has($key)
    {
        return !is_null(self::get($key));
    }

    public static function set($key, $value = null)
    {
        self::checkInit();

        if (is_array($key)) {
            foreach ($key as $realKey => $realValue) {
                $_SESSION[$realKey] = $realValue;
            }
        } else {
            $_SESSION[$key] = $value;
        }
    }

    public static function delete($key)
    {
        self::checkInit();

        if (is_array($key)) {
            foreach ($key as $realKey) {
                unset($_SESSION[$realKey]);
            }
        } else {
            unset($_SESSION[$key]);
        }
    }
}
<?php

namespace bestlang\core;

class BLConfig
{
    private static $_config = [];

    private static function loadConfigFile() {
        $file = APP_ROOT . 'config.php';
        if (file_exists($file)) {
            self::$_config = include $file;
        }
    }

    public static function get(...$path) {
        if (empty(self::$_config)) {
            self::loadConfigFile();
        }

        $ret = self::$_config;
        foreach ($path as $node) {
            if (isset($ret[$node])) {
                $ret = $ret[$node];
            } else {
                return '';
            }
        }
        return $ret;
    }
}
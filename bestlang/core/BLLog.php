<?php

namespace bestlang\core;


class BLLog
{
    /**
     * 向控制台输出日志
     * @param string|mixed $content 要输出的对象
     */
    public static function log($content) {
        if (is_string($content)) {
            $log = $content;
        } else {
            $log = var_export($content, true);
        }
        file_put_contents('php://stdout', $log . "\n");
    }
}
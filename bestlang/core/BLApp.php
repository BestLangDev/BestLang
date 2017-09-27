<?php

namespace bestlang\core;

use bestlang\core\util\BLResponse;

/**
 * 应用程序类
 * @package bestlang\core
 */
class BLApp
{
    public static function start()
    {
        self::route();
    }

    /**
     * 路由方案：
     * (1) / -> Main.php::index()
     * (2) /aaa -> Aaa.php::index()
     * (3) /aaa/bbb/ccc -> aaa/bbb.php::ccc() -> aaa/bbb/Ccc.php::index()
     */
    private static function route()
    {
        $path = isset($_SERVER['PATH_INFO']) ? explode('/', trim($_SERVER['PATH_INFO'], '/')) : [];
        BLLog::log(var_export($path, true));
        $size = sizeof($path);
        if ($size <= 0) {
            // (1)
            $result = self::tryInvoke(DEFAULT_CONTROLLER, DEFAULT_METHOD);
        } elseif ($size == 1) {
            // (2)
            $result = self::tryInvoke($path[0] ?: DEFAULT_CONTROLLER, DEFAULT_METHOD);
        } else {
            // (3)
            $result = self::tryInvoke($path[$size - 2], $path[$size - 1], join('/', array_slice($path, 0, $size - 2)));
            if (is_null($result)) {
                $result = self::tryInvoke($path[$size - 1], DEFAULT_METHOD, join('/', array_slice($path, 0, $size - 1)));
            }
        }
        // check again
        if ($result === false) {
            BLLog::log('Cannot find callable for path ' . $_SERVER['REQUEST_URI']);
            echo 'Error';
            return; // TODO 500
        }
        // output
        if (is_a($result, BLResponse::class)) {
            http_response_code($result->getStatus());
            header('Content-Type:' . $result->getContentType());
            echo $result->getBody();
        } else {
            echo $result;
        }
    }

    private static function load_controller($filename)
    {
        include_once APP_CONTROLLER_DIR . $filename . '.php';
    }

    private static function tryInvoke($controller, $method, $path = '')
    {
        $class = ucfirst($controller);
        $rClass = self::getClass('app\\controller\\' . ($path ? $path . '\\' : '') . $class);
        if (is_null($rClass)) {
            return false;
        }
        try {
            $method = $rClass->getMethod($method);
            if (!$method->isPublic()) {
                return false;
            }
            return $method->invoke($rClass->newInstance());
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    private static function getClass($class)
    {
        try {
            return new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}
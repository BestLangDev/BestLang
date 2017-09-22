<?php

namespace bestlang\core;

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
        $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));
        // BLLog::log(var_export($path, true));
        $size = sizeof($path);
        if ($size <= 0) {
            // (1)
            $class = ucfirst(DEFAULT_CONTROLLER);
            $method = DEFAULT_METHOD;
            self::load_controller($class);
        } elseif ($size == 1) {
            // (2)
            $class = ucfirst($path[0] ?: DEFAULT_CONTROLLER);
            $method = DEFAULT_METHOD;
            self::load_controller($class);
        } else {
            // (3)
            $class = ucfirst($path[$size - 2]);
            $method = $path[$size - 1];
            self::load_controller(join('/', array_slice($path, 0, $size - 2)) . '/' . $class);
            if (!class_exists($class, false) || !method_exists($class, $method)) {
                $class = ucfirst($path[$size - 1]);
                $method = DEFAULT_METHOD;
                self::load_controller($filename = join('/', array_slice($path, 0, $size - 1)) . '/' . $class);
            }
        }
        // check again
        if (!class_exists($class, false) || !method_exists($class, $method)) {
            BLLog::log('Cannot find callable for path ' . $_SERVER['PATH_INFO']);
            return; // TODO 500
        }
        BLLog::log('Calling ' . $class . '::' . $method);
        $controller = new $class();
        echo call_user_func([$controller, $method]);
    }

    private static function load_controller($filename)
    {
        include_once APP_CONTROLLER_DIR . $filename . '.php';
    }
}
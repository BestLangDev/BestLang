<?php

namespace bestlang\core;


class BLApp
{
    public static function start()
    {
        self::route();
    }

    /**
     * 路由方案：
     * (1) / -> Main.php::main()
     * (2) /aaa -> Aaa.php::main()
     * (3) /aaa/bbb/ccc -> aaa/bbb.php::ccc() -> aaa/bbb/Ccc.php::main()
     */
    private static function route()
    {
        $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));
        // BLLog::log(var_export($path, true));
        $size = sizeof($path);
        if ($size <= 0) {
            // (1)
            $class = ucfirst(DEFAULT_NAME);
            $method = DEFAULT_NAME;
            self::load_controller($class);
        } elseif ($size == 1) {
            // (2)
            $class = ucfirst($path[0] ?: DEFAULT_NAME);
            $method = DEFAULT_NAME;
            self::load_controller($class);
        } else {
            // (3)
            $class = ucfirst($path[$size - 2]);
            $method = $path[$size - 1];
            self::load_controller(join('/', array_slice($path, 0, $size - 2)) . '/' . $class);
            if (!class_exists($class, false) || !method_exists($class, $method)) {
                $class = ucfirst($path[$size - 1]);
                $method = DEFAULT_NAME;
                self::load_controller($filename = join('/', array_slice($path, 0, $size - 1)) . '/' . $class);
            }
        }
        // check again
        if (!class_exists($class, false) || !method_exists($class, $method)) {
            BLLog::log('Cannot find callable ' . $class . '::' . $method);
            return; // TODO 500
        }
        BLLog::log('Calling ' . $class . '::' . $method);
        echo call_user_func([$class, $method]);
    }

    private static function load_controller($filename)
    {
        include_once APP_CONTROLLER_DIR . $filename . '.php';
    }
}
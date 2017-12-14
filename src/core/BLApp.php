<?php

namespace BestLang\core;

use BestLang\core\controller\BLInterceptor;
use BestLang\core\util\BLResponse;

/**
 * 应用程序类
 * @package bestlang\core
 */
class BLApp
{
    public static function start()
    {
        // 定义常量
        defined('APP_ROOT') or define('APP_ROOT', __DIR__ . '/../../../../../app/');
        define('APP_CONTROLLER_DIR', APP_ROOT . 'controller/');
        define('APP_MODEL_DIR', APP_ROOT . 'model/');
        define('APP_VIEW_DIR', APP_ROOT . 'view/');
        define('APP_CACHE_DIR', APP_ROOT . 'cache/');
        define('DEFAULT_CONTROLLER', 'main');
        define('DEFAULT_METHOD', 'index');

        // 注册类加载器
        spl_autoload_register(function ($class) {
            file_put_contents('php://stdout', 'Loading class ' . $class . "\n");
            // 适配 Unix
            $class = str_replace('\\', '/', $class);
            // 根据 namespace 搜索
            if (strpos($class, 'app/') === 0) {
                return include_once APP_ROOT . substr($class, 4) . '.php';
            }
            // 自动查找
            if (file_exists(APP_CONTROLLER_DIR . $class . '.php')) {
                return include_once APP_CONTROLLER_DIR . $class . '.php';
            }
            if (file_exists(APP_MODEL_DIR . $class . '.php')) {
                return include_once APP_MODEL_DIR . $class . '.php';
            }
            return false;
        });

        // 真正启动应用
        BLLog::log('[' . $_SERVER['REQUEST_METHOD'] . '] ' . $_SERVER['REQUEST_URI']);
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
        // Interceptor
        $interceptor = BLConfig::get('interceptor');
        if (!empty($interceptor)) {
            $interceptorClass = self::getClass($interceptor);
            if (!is_null($interceptorClass) && $interceptorClass->implementsInterface(BLInterceptor::class)) {
                $interceptor = $interceptorClass->newInstance();
            }
            $interceptor->before();
        }

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

        if (!empty($interceptor)) {
            $interceptor->after();
        }

        // check again
        if ($result === false) {
            BLLog::log('Cannot find callable for path ' . $_SERVER['REQUEST_URI']);
            http_response_code(404);
            include 'template/not_found.php';
        }
        // output
        if ($result instanceof BLResponse) {
            http_response_code($result->getStatus());
            header('Content-Type:' . $result->getContentType());
            echo $result->getBody();
        } elseif ($result instanceof \Exception) {
            http_response_code(500);
            include 'template/exception.php';
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
            try {
                return $method->invoke($rClass->newInstance());
            } catch (\Exception $e) {
                return $e;
            }
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
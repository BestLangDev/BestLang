<?php

/**
 * 框架入口文件
 */

// 定义常量
define('BL_ROOT', __DIR__ . '/');
define('APP_CONTROLLER_DIR', APP_ROOT . 'controller/');
define('APP_MODEL_DIR', APP_ROOT . 'model/');
define('APP_VIEW_DIR', APP_ROOT . 'view/');
define('DEFAULT_CONTROLLER', 'main');
define('DEFAULT_METHOD', 'index');

// 注册类加载器
spl_autoload_register(function ($class) {
    file_put_contents('php://stdout', 'Loading class ' . $class . "\n");
    // 适配 Unix
    $class = str_replace('\\', '/', $class);
    // 根据 namespace 搜索
    if (strpos($class, 'bestlang/') === 0) {
        return include_once BL_ROOT . substr($class, 9) . '.php';
    }
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
\bestlang\core\BLLog::log('[' . $_SERVER['REQUEST_METHOD'] . '] ' . $_SERVER['REQUEST_URI']);
\bestlang\core\BLApp::start();

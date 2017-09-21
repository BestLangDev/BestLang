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
    if (strpos($class, 'bestlang\\') !== 0) {
        return false;
    }
    $file = substr($class, 9);
    return require_once BL_ROOT . $file . '.php';
});

// 真正启动应用
bestlang\core\BLLog::log('[' . $_SERVER['REQUEST_METHOD'] . '] ' . $_SERVER['REQUEST_URI']);
bestlang\core\BLApp::start();

<?php

/**
 * 框架入口文件
 */

// 定义常量
defined('APP_ROOT') or define('APP_ROOT', __DIR__ . '/../../../app/');
define('BL_ROOT', __DIR__ . '/');
define('APP_CONTROLLER_DIR', APP_ROOT . 'controller/');
define('APP_MODEL_DIR', APP_ROOT . 'model/');
define('APP_VIEW_DIR', APP_ROOT . 'view/');
define('DEFAULT_CONTROLLER', 'main');
define('DEFAULT_METHOD', 'index');

// 真正启动应用
\BestLang\core\BLLog::log('[' . $_SERVER['REQUEST_METHOD'] . '] ' . $_SERVER['REQUEST_URI']);
\BestLang\core\BLApp::start();

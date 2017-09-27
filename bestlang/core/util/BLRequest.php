<?php

namespace bestlang\core\util;

class BLRequest
{
    /**
     * @return string 请求方法
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return string 主机名
     */
    public static function host()
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * @return string 请求完全路径（包含查询字符串）
     */
    public static function fullPath()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string 请求真实路径
     */
    public static function realPath()
    {
        return $_SERVER['PATH_INFO'];
    }

    /**
     * @return string 查询字符串
     */
    public static function queryStr()
    {
        return $_SERVER['QUERY_STRING'];
    }

    /**
     * @return array 全部请求头
     */
    public static function headers()
    {
        return getallheaders();
    }

    /**
     * @return mixed 请求体
     */
    public static function body()
    {
        return file_get_contents('php://input');
    }

    private static function getOrDefault($map, $key, $default)
    {
        return isset($map[$key]) ? $map[$key] : $default;
    }

    /**
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @return mixed GET 参数
     */
    public static function get($key, $default = null)
    {
        return self::getOrDefault($_GET, $key, $default);
    }

    /**
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @return mixed POST 参数
     */
    public static function post($key, $default = null)
    {
        return self::getOrDefault($_POST, $key, $default);
    }
}
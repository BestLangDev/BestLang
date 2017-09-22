<?php

namespace bestlang\core;


class BLRequest extends Singleton
{
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function host()
    {
        return $_SERVER['HTTP_HOST'];
    }

    public function fullPath()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function realPath()
    {
        return $_SERVER['PATH_INFO'];
    }

    public function queryStr()
    {
        return $_SERVER['QUERY_STRING'];
    }

    public function headers()
    {
        return getallheaders();
    }

    public function body()
    {
        return file_get_contents('php://input');
    }

    private function getOrDefault($map, $key, $default = null)
    {
        return isset($map[$key]) ? $map[$key] : $default;
    }

    public function get($key, $default = null)
    {
        return $this->getOrDefault($_GET, $key, $default);
    }

    public function post($key, $default = null)
    {
        return $this->getOrDefault($_POST, $key, $default);
    }
}
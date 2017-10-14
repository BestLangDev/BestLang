<?php

namespace bestlang\ext\cache;

class WinCache extends BLCache
{
    function isAvailable()
    {
        return function_exists('wincache_ucache_info');
    }

    function hasOne($key)
    {
        return wincache_ucache_exists($key);
    }

    function getOne($key)
    {
        return wincache_ucache_get($key);
    }

    function setOne($key, $value, $ttl = 0)
    {
        return wincache_ucache_set($key, $value, $ttl);
    }

    function deleteOne($key)
    {
        return wincache_ucache_delete($key);
    }
}
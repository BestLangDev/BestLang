<?php

namespace BestLang\ext\cache;

class CacheStub extends BLCache
{
    private $_cache = [];
    private $_ttl = [];

    function isAvailable()
    {
        return true;
    }

    function hasOne($key)
    {
        if (!isset($this->_cache[$key])) {
            return false;
        }
        if ($this->_ttl[$key] > 0 && $this->_ttl[$key] > time()) {
            return false;
        }
        return true;
    }

    function getOne($key)
    {
        return $this->hasOne($key) ? $this->_cache[$key] : null;
    }

    function setOne($key, $value, $ttl = 0)
    {
        $this->_cache[$key] = $value;
        $this->_ttl[$key] = ($ttl != 0) ? (time() + $ttl) : 0;
    }

    function deleteOne($key)
    {
        unset($this->_cache[$key]);
        unset($this->_ttl[$key]);
    }
}
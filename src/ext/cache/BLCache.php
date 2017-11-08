<?php

namespace BestLang\ext\cache;


use BestLang\core\BLConfig;

abstract class BLCache
{
    abstract function isAvailable();

    abstract function hasOne($key);

    abstract function getOne($key);

    abstract function setOne($key, $value, $ttl = 0);

    abstract function deleteOne($key);

    private static $_providers = [
        WinCache::class,
        CacheStub::class
    ];

    private static $_handle;

    private static function checkInit()
    {
        if (!isset(self::$_handle)) {
            self::$_handle = self::getHandle();
            if (!isset(self::$_handle)) {
                throw new \Exception('No cache providers found');
            }
        }
    }

    private static function getHandle()
    {
        $configProvider = BLConfig::get('cache', 'provider');
        if (!empty($configProvider)) {
            try {
                $handle = (new \ReflectionClass($configProvider))->newInstance();
                return $handle;
            } catch (\Exception $e) {
            }
        }

        foreach (self::$_providers as $provider) {
            try {
                $handle = (new \ReflectionClass($provider))->newInstance();
                if ($handle->isAvailable()) {
                    return $handle;
                }
            } catch (\Exception $e) {
            }
        }
        return null;
    }

    public static function get($key = null, $default = null)
    {
        self::checkInit();

        $result = self::$_handle->getOne($key);
        return isset($result) ? $result : $default;
    }

    public static function has($key)
    {
        return self::$_handle->hasOne($key);
    }

    public static function set($key, $value = null)
    {
        self::checkInit();

        if (is_array($key)) {
            foreach ($key as $realKey => $realValue) {
                self::$_handle->setOne($realKey, $realValue);
            }
        } else {
            self::$_handle->setOne($key, $value);
        }
    }

    public static function delete($key)
    {
        self::checkInit();

        if (is_array($key)) {
            foreach ($key as $realKey) {
                self::$_handle->deleteOne($realKey);
            }
        } else {
            self::$_handle->deleteOne($key);
        }
    }
}
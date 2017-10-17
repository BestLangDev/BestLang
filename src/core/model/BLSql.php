<?php

namespace BestLang\core\model;

use BestLang\core\BLConfig;
use BestLang\core\BLLog;

class BLSql
{
    private static $_dbhnd;

    /**
     * 获取数据库连接
     * @return \PDO
     */
    public static function getHandle()
    {
        if (!isset(self::$_dbhnd)) {
            self::$_dbhnd = new \PDO(
                BLConfig::get('db', 'dsn'),
                BLConfig::get('db', 'user'),
                BLConfig::get('db', 'pass')
            );
            self::$_dbhnd->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$_dbhnd;
    }

    /**
     * 执行 SQL 语句
     * @param string $sql SQL 语句
     * @param array $params 绑定参数
     * @return \PDOStatement 执行结果
     */
    public static function exec($sql, $params = [])
    {
        BLLog::log('[SQL] ' . $sql);
        BLLog::log('[Params] ' . var_export($params, true));
        $stmt = self::getHandle()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
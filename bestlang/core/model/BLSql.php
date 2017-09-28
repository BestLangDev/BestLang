<?php

namespace bestlang\core\model;

use app\config\DBConfig;
use bestlang\core\BLLog;

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
            try {
                self::$_dbhnd = new \PDO(DBConfig::$dsn, DBConfig::$user, DBConfig::$pass);
                self::$_dbhnd->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
            }
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
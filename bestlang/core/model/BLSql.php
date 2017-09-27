<?php

namespace bestlang\core\model;

use app\config\DBConfig;

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
            } catch (\PDOException $e) {
            }
        }
        return self::$_dbhnd;
    }

    /**
     * 执行 SQL 查询
     * @param string $sql SQL 语句
     * @param array $params 绑定参数
     * @return mixed 查询结果
     */
    public static function query($sql, $params = [])
    {
        $stmt = self::getHandle()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
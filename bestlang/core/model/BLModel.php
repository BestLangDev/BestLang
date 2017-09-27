<?php

namespace bestlang\core\model;

class BLModel
{
    /**
     * @var string 表名
     */
    protected static $table;

    /**
     * @var array 表结构
     */
    protected static $fields;

    /**
     * @var string 主键列名
     */
    protected static $pk;

    public static function table()
    {
        if (!isset(static::$table)) {
            static::$table = strtolower((new \ReflectionClass(static::class))->getShortName());
        }
        return static::$table;
    }

    public static function fields()
    {
        if (!isset(static::$fields)) {
            self::getTableInfo();
        }
        return static::$fields;
    }

    private static function getTableInfo()
    {
        $sql = 'SHOW COLUMNS FROM ' . self::table();
        static::$fields = [];
        foreach (BLSql::query($sql)->fetchAll() as $row) {
            static::$fields[] = $row['Field'];
            if (strtolower($row['Key']) == 'pri') {
                static::$pk = $row['Field'];
            }
        }
        return static::$fields;
    }
}
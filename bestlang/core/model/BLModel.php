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
    protected static $pkfield;

    // ========== 表信息相关方法 ==========

    /**
     * 获取该 Model 的表名
     * @return string
     */
    public static function table()
    {
        if (!isset(static::$table)) {
            static::$table = strtolower((new \ReflectionClass(static::class))->getShortName());
        }
        return static::$table;
    }

    /**
     * 获取该 Model 的所有列，或手工指定使用的列
     * @param array $custom
     * @return array
     */
    public static function fields($custom = null)
    {
        if (is_array($custom)) {
            static::$fields = $custom;
        } elseif (!isset(static::$fields)) {
            self::getTableInfo();
        }
        return static::$fields;
    }

    /**
     * 获取该 Model 主键列名
     * @return string|false
     */
    public static function pkfield()
    {
        if (!isset(static::$pkfield)) {
            self::getTableInfo();
        }
        return static::$pkfield;
    }

    private static function getTableInfo()
    {
        $sql = 'SHOW COLUMNS FROM ' . self::table() . ';';
        static::$fields = [];
        static::$pkfield = false;
        foreach (BLSql::exec($sql)->fetchAll() as $row) {
            static::$fields[] = $row['Field'];
            if (strtolower($row['Key']) == 'pri') {
                static::$pkfield = $row['Field'];
            }
        }
        return static::$fields;
    }

    // ========== CRUD 相关方法 ==========

    public static function insert($data = [])
    {
        $realData = self::filterData($data);
        $fields = [];
        $quests = [];
        $params = [];
        foreach ($realData as $field => $value) {
            $fields[] = '`' . $field . '`';
            $quests[] = '?';
            $params[] = $value;
        }
        $sql = 'INSERT INTO `' . self::table() . '` (' . join(',', $fields) . ') VALUES (' . join(',', $quests) . ');';
        return BLSql::exec($sql, $params);
    }

    public static function delete($pk)
    {
        if (empty(self::pkfield())) {
            return false;
        }
        $sql = 'DELETE FROM `' . self::table() . '` WHERE `' . self::pkfield() . '` = ?;';
        return BLSql::exec($sql, [$pk]);
    }

    private static function filterData($src = [])
    {
        $result = [];
        if (is_array($src)) {
            foreach (self::fields() as $field) {
                if (isset($src[$field])) {
                    $result[$field] = $src[$field];
                }
            }
        }
        return $result;
    }
}
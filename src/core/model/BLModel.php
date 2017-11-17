<?php

namespace BestLang\core\model;

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
    protected static $pkField;

    /**
     * @var array 数据
     */
    private $_data;

    /**
     * @var array 修改标记
     */
    private $_dirty = [];

    /**
     * @var mixed 主键值
     */
    private $_pkValue;

    /**
     * BLModel constructor.
     * @param array $data
     * @param bool $setPkValue
     */
    public function __construct($data = null, $setPkValue = false)
    {
        if (isset($data)) {
            $this->data($data, false);
            if ($setPkValue) {
                $this->_pkValue = $this->_data[self::pkField()];
            }
        }
    }

    public function __get($name)
    {
        return $this->_data[strtolower($name)];
    }

    public function __set($name, $value)
    {
        $this->data([strtolower($name) => $value]);
    }

    // ========== 实例方法 ==========

    /**
     * 获取 / 设置实例数据
     * @param array $data
     * @param bool $setDirty
     * @return array|bool
     */
    public function data($data = null, $setDirty = true)
    {
        if (isset($data)) {
            if (is_array($data)) {
                foreach (self::fields() as $field) {
                    if (isset($data[$field])) {
                        $this->_data[$field] = $data[$field];
                        if ($setDirty) {
                            $this->_dirty[$field] = true;
                        }
                    }
                }
                return true;
            }
            return false;
        } else {
            return $this->_data;
        }
    }

    /**
     * 将模型写入数据库
     * @return int 若为插入，返回新主键值；若为更新，返回受影响行数
     */
    public function save()
    {
        if (isset($this->_pkValue)) {
            // Update
            $quests = [];
            $params = [];
            foreach ($this->_dirty as $field => $_) {
                if ($field != self::pkField()) {
                    $quests[] = '`' . $field . '`=?';
                    $params[] = $this->_data[$field];
                }
            }
            $sql = 'UPDATE `' . self::table() . '` SET ' . join(',', $quests) . ' WHERE `' . self::pkField() . '`=?;';
            $params[] = $this->_pkValue;
            $result = BLSql::exec($sql, $params)->rowCount();
            if ($result > 0) {
                $this->_dirty = [];
            }
            return $result;
        } else {
            // Insert
            $fields = [];
            $quests = [];
            $params = [];
            foreach ($this->_data as $field => $value) {
                $fields[] = '`' . $field . '`';
                $quests[] = '?';
                $params[] = $value;
            }
            $sql = 'INSERT INTO `' . self::table() . '` (' . join(',', $fields) . ') VALUES (' . join(',', $quests) . ');';
            BLSql::exec($sql, $params);
            $this->_dirty = [];
            // save pk value
            $this->_pkValue = BLSql::getHandle()->lastInsertId(self::pkField());
            $this->_data[self::pkField()] = $this->_pkValue;
            return $this->_pkValue;
        }
    }

    // ========== 表信息相关方法 ==========

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
     * 获取该 Model 的表名
     * @return string
     */
    private static function table()
    {
        if (!isset(static::$table)) {
            static::$table = strtolower((new \ReflectionClass(static::class))->getShortName());
        }
        return static::$table;
    }

    /**
     * 获取该 Model 主键列名
     * @return string|false
     */
    private static function pkField()
    {
        if (!isset(static::$pkField)) {
            self::getTableInfo();
        }
        return static::$pkField;
    }

    private static function getTableInfo()
    {
        static::$fields = [];
        static::$pkField = false;
        switch (BLSql::dbType()) {
            case 'sqlite':
                return self::getTableInfoSQLite();
            default:
                return self::getTableInfoGeneric();
        }
    }

    private static function getTableInfoGeneric()
    {
        $sql = 'SHOW COLUMNS FROM ' . self::table() . ';';
        foreach (BLSql::exec($sql)->fetchAll() as $row) {
            static::$fields[] = strtolower($row['Field']);
            if (strtolower($row['Key']) == 'pri') {
                static::$pkField = $row['Field'];
            }
        }
    }

    private static function getTableInfoSQLite()
    {
        $sql = 'PRAGMA table_info(' . self::table() . ');';
        foreach (BLSql::exec($sql)->fetchAll() as $row) {
            static::$fields[] = strtolower($row['name']);
            if ($row['pk'] == 1) {
                static::$pkField = $row['name'];
            }
        }
    }

    // ========== 便捷方法 ==========

    public static function get($pk)
    {
        if (empty(self::pkField())) {
            return false;
        }
        $sql = 'SELECT ' . join(',', self::fields()) . ' FROM `' . self::table() . '` WHERE `' . self::pkField() . '`=?;';
        $result = BLSql::exec($sql, [$pk]);
        return new static($result->fetch(\PDO::FETCH_ASSOC), true);
    }

    public static function all()
    {
        $sql = 'SELECT ' . join(',', self::fields()) . ' FROM `' . self::table() . '`;';
        $result = BLSql::exec($sql);
        $models = [];
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $models[] = new static($row, true);
        }
        return $models;
    }

    public static function query()
    {
        return new BLQuery(static::class, self::table(), self::fields());
    }

    public static function insert($data = [])
    {
        return (new static($data))->save();
    }

    public static function delete($pk)
    {
        if (empty(self::pkField())) {
            return false;
        }
        $sql = 'DELETE FROM `' . self::table() . '` WHERE `' . self::pkField() . '` = ?;';
        return BLSql::exec($sql, [$pk])->rowCount();
    }
}
<?php

namespace BestLang\core\model;

class BLModel implements \JsonSerializable
{
    /**
     * @var string 表名
     */
    protected static $table;
    private static $tableMap = [];

    /**
     * @var array 表结构
     */
    protected static $fields;
    private static $fieldsMap = [];

    /**
     * @var string 主键列名
     */
    protected static $pkField;
    private static $pkFieldMap = [];

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

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->_data;
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
                    if (key_exists($field, $data)) {
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
            if (BLSql::exec($sql, $params)->rowCount() <= 0) {
                return false;
            }
            $this->_dirty = [];
            // save pk value
            $this->_pkValue = BLSql::getHandle()->lastInsertId(self::pkField());
            $this->_data[self::pkField()] = $this->_pkValue;
            return $this->_pkValue;
        }
    }

    /**
     * 从数据库中删除此模型
     * @return bool
     */
    public function remove()
    {
        if (!isset($this->_pkValue)) {
            return false;
        }
        if (self::delete($this->_pkValue) > 0) {
            $this->_pkValue = null;
            return true;
        }
        return false;
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
            self::$fieldsMap[static::class] = $custom;
        } elseif (!isset(self::$fieldsMap[static::class])) {
            if (isset(static::$fields)) {
                self::$fieldsMap[static::class] = static::$fields;
            } else {
                self::getTableInfo();
            }
        }
        return self::$fieldsMap[static::class];
    }

    /**
     * 获取该 Model 的表名
     * @return string
     */
    private static function table()
    {
        if (!isset(self::$tableMap[static::class])) {
            if (isset(static::$table)) {
                self::$tableMap[static::class] = static::$table;
            } else {
                self::$tableMap[static::class] = strtolower((new \ReflectionClass(static::class))->getShortName());
            }
        }
        return self::$tableMap[static::class];
    }

    /**
     * 获取该 Model 主键列名
     * @return string|false
     */
    private static function pkField()
    {
        if (!isset(self::$pkFieldMap[static::class])) {
            if (isset(static::$pkField)) {
                self::$pkFieldMap[static::class] = static::$pkField;
            } else {
                self::getTableInfo();
            }
        }
        return self::$pkFieldMap[static::class];
    }

    private static function getTableInfo()
    {
        self::$fieldsMap[static::class] = [];
        self::$pkFieldMap[static::class] = false;
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
            self::$fieldsMap[static::class][] = strtolower($row['Field']);
            if (strtolower($row['Key']) == 'pri') {
                self::$pkFieldMap[static::class] = $row['Field'];
            }
        }
    }

    private static function getTableInfoSQLite()
    {
        $sql = 'PRAGMA table_info(' . self::table() . ');';
        foreach (BLSql::exec($sql)->fetchAll() as $row) {
            self::$fieldsMap[static::class][] = strtolower($row['name']);
            if ($row['pk'] == 1) {
                self::$pkFieldMap[static::class] = $row['name'];
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
        $data = $result->fetch(\PDO::FETCH_ASSOC);
        if ($data === false) {
            return false;
        }
        return new static($data, true);
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
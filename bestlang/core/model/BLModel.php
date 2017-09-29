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
    protected static $pkField;

    /**
     * @var array 数据
     */
    private $data;

    /**
     * @var mixed 主键值
     */
    private $pkValue;

    /**
     * BLModel constructor.
     * @param array $data
     */
    public function __construct($data = null)
    {
        if (isset($data)) {
            $this->data($data);
        }
    }

    public function __get($name)
    {
        return $this->data[strtolower($name)];
    }

    public function __set($name, $value)
    {
        $this->data([strtolower($name) => $value]);
    }

    // ========== 实例方法 ==========

    /**
     * 获取 / 设置实例数据
     * @param array $data
     * @return array|bool
     */
    public function data($data = null)
    {
        if (isset($data)) {
            if (is_array($data)) {
                foreach (self::fields() as $field) {
                    if (isset($data[$field])) {
                        $this->data[$field] = $data[$field];
                    }
                }
                return true;
            }
            return false;
        } else {
            return $this->data;
        }
    }

    /**
     * 将模型写入数据库
     */
    public function save()
    {
        if (isset($this->pkValue)) {
            // Update
            $quests = [];
            $params = [];
            foreach ($this->data as $field => $value) {
                if ($field != self::pkField()) {
                    $quests[] = '`' . $field . '`=?';
                    $params[] = $value;
                }
            }
            $sql = 'UPDATE `' . self::table() . '` SET ' . join(',', $quests) . ' WHERE `' . self::pkField() . '`=?;';
            $params[] = $this->pkValue;
            return BLSql::exec($sql, $params)->rowCount();
        } else {
            // Insert
            $fields = [];
            $quests = [];
            $params = [];
            foreach ($this->data as $field => $value) {
                $fields[] = '`' . $field . '`';
                $quests[] = '?';
                $params[] = $value;
            }
            $sql = 'INSERT INTO `' . self::table() . '` (' . join(',', $fields) . ') VALUES (' . join(',', $quests) . ');';
            BLSql::exec($sql, $params);
            // save pk value
            $this->pkValue = BLSql::getHandle()->lastInsertId(self::pkField());
            $this->data[self::pkField()] = $this->pkValue;
            return $this->pkValue;
        }
    }

    private function autoPkValue($value = null)
    {
        if (isset($value)) {
            $this->pkValue = $value;
        } else {
            $this->pkValue = $this->data[self::pkField()];
        }
    }

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
    public static function pkField()
    {
        if (!isset(static::$pkField)) {
            self::getTableInfo();
        }
        return static::$pkField;
    }

    private static function getTableInfo()
    {
        $sql = 'SHOW COLUMNS FROM ' . self::table() . ';';
        static::$fields = [];
        static::$pkField = false;
        foreach (BLSql::exec($sql)->fetchAll() as $row) {
            static::$fields[] = strtolower($row['Field']);
            if (strtolower($row['Key']) == 'pri') {
                static::$pkField = $row['Field'];
            }
        }
        return static::$fields;
    }

    // ========== 便捷方法 ==========

    public static function get($pk)
    {
        if (empty(self::pkField())) {
            return false;
        }
        $sql = 'SELECT * FROM `' . self::table() . '` WHERE `' . self::pkField() . '`=?;';
        $result = BLSql::exec($sql, [$pk]);
        $model = new static($result->fetch(\PDO::FETCH_ASSOC));
        $model->autoPkValue();
        return $model;
    }

    public static function all()
    {
        $sql = 'SELECT * FROM `' . self::table() . '`;';
        $result = BLSql::exec($sql);
        $models = [];
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $model = new static($row);
            $model->autoPkValue();
            $models[] = $model;
        }
        return $models;
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
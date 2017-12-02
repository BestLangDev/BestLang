<?php

namespace BestLang\core\model;

class BLQuery
{
    private $modelClass;

    private $table;

    private $fields;

    private $wheres = [];

    private $params = [];

    private $orders = [];

    private $limit;

    /**
     * BLQuery constructor.
     * @param $model
     * @param $table
     * @param $fields
     */
    public function __construct($model, $table, $fields)
    {
        $this->modelClass = new \ReflectionClass($model);
        $this->table = $table;
        $this->fields = $fields;
    }

    public function get()
    {
        $sql = 'SELECT ' . join(',', $this->fields) . ' FROM `' . $this->table . '`';
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . join(' AND ', $this->wheres);
        }
        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . join(', ', $this->orders);
        }
        if (!empty($this->limit)) {
            $sql .= ' LIMIT ' . $this->limit;
        }
        $result = BLSql::exec($sql, $this->params);
        $models = [];
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $models[] = $this->modelClass->newInstance($row, true);
        }
        return $models;
    }

    /**
     *    where(field, op, value)
     * OR where(field, value) (default op is "=")
     * @param $field
     * @param $arg2
     * @param $arg3
     * @return $this
     */
    public function where($field, $arg2, $arg3 = null)
    {
        if (is_null($arg3)) {
            $op = '=';
            $value = $arg2;
        } else {
            $op = $arg2;
            $value = $arg3;
        }
        $this->wheres[] = '`' . $field . '` ' . $op . ' ?';
        $this->params[] = $value;
        return $this;
    }

    public function whereOp($field, $op)
    {
        $this->wheres[] = '`' . $field . '` ' . $op;
        return $this;
    }

    /**
     * @param $order
     * @return $this
     */
    public function orderBy($order)
    {
        $this->orders[] = $order;
        return $this;
    }

    public function limit($start, $end = null)
    {
        if (is_null($end)) {
            $this->limit = $start;
        } else {
            $this->limit = $start . ',' . $end;
        }
        return $this;
    }
}
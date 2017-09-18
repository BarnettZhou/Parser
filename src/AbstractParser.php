<?php

namespace Xuchen\Parser;


abstract class AbstractParser
{
    /**
     * 整理单条数据的方法
     * @param array $row
     * @return array
     */
    abstract public function parseSingleRow(array $row);

    /**
     * 整理数据时的规则（单个字段）
     * @return array
     */
    abstract protected function parseRules();

    protected $parse_mode = self::PARSE_MODE_ONLY;

    /**
     * 返回模式，默认返回参数列表中的数据
     * @var int
     */
    protected $return_mode = self::RETURN_MODE_WITH_KEYS;

    /**
     * 数据模式，默认为多行模式
     * @var int
     */
    protected $row_mode = self::ROW_MODE_MANY;

    /**
     * 所有数据
     * @var array
     */
    protected $rows = [];

    /**
     * @var array 原数据中的字段
     */
    protected $original_keys = [];

    /**
     * 字段检查模式
     * PARSE_MODE_ALL       : 所有字段都被整理
     * PARSE_MODE_ONLY      : 只整理指定的字段
     * PARSE_MODE_EXCEPT    : 检查除了指定字段外的所有字段
     */
    const PARSE_MODE_ALL        = 0;
    const PARSE_MODE_ONLY       = 1;
    const PARSE_MODE_EXCEPT     = 2;

    /**
     * 返回模式
     */
    const RETURN_MODE_ALL       = 0;
    const RETURN_MODE_WITH_KEYS = 1;

    /**
     * 数据形式，单行数据或多行数据
     */
    const ROW_MODE_SINGLE   = 0;
    const ROW_MODE_MANY     = 1;

    /**
     * 设置rows
     * @param $rows
     * @return $this
     */
    public function setRows($rows)
    {
        $this->rows     = $rows;
        $this->row_mode = self::ROW_MODE_MANY;
        return $this;
    }

    /**
     * 返回rows
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * 设置单行数据
     * @param $row
     * @return $this
     */
    public function setSingleRow($row)
    {
        $this->row_mode = self::ROW_MODE_SINGLE;
        $this->rows     = [$row];
        return $this;
    }

    /**
     * 返回单行数据
     * @return array
     */
    public function getSingleRow()
    {
        if (isset($this->rows[0]) && is_array($this->rows[0])) {
            return $this->rows[0];
        } else {
            return [];
        }
    }

    /**
     * 设置整理模式
     * @param $mode
     * @return $this
     */
    final public function setParseMode($mode)
    {
        if (in_array($mode, [0, 1, 2])) {
            $this->parse_mode = $mode;
        }
        return $this;
    }

    /**
     * 设置返回模式
     * @param $mode
     * @return $this
     */
    final public function setReturnMode($mode)
    {
        if (in_array($mode, [0, 1])) {
            $this->return_mode = $mode;
        }
        return $this;
    }

    /**
     * 设置数据模式
     * @param $mode
     * @return $this
     */
    final public function setRowMode($mode)
    {
        if (in_array($mode, [0, 1])) {
            $this->return_mode = $mode;
        }
        return $this;
    }

    /**
     * 返回的数据集中带上原数据
     * @param array $except
     * @return $this
     */
    final public function withOriginalAttributes($except = [])
    {
        if (count($this->rows) > 0) {
            $row = $this->rows[0];
        } else {
            return $this;
        }

        $this->original_keys = array_diff(array_keys($row), $except);
        return $this;
    }

    /**
     * 根据给出的整理规则整理数据
     * @param array $keys
     * @return array
     */
    final public function parseWithRules($keys = [])
    {
        $this->beforeParseRows();

        $parse_mode     = $this->parse_mode;
        $return_mode    = $this->return_mode;

        $rules = array_keys($this->parseRules());
        // ONLY - MODE
        if ($parse_mode == self::PARSE_MODE_ONLY) {
            $rules_to_parse = array_merge($keys, $this->original_keys);
        // EXCEPT - MODE
        } else if ($parse_mode == self::PARSE_MODE_EXCEPT) {
            $rules_to_parse = array_diff(array_merge($keys, $this->original_keys), $keys);
        // ALL - MODE
        } else {
            $rules_to_parse = array_merge($rules, $this->original_keys);
        }

        foreach ($this->rows as $key => $row) {
            foreach ($rules_to_parse as $_func_name) {
                if (!in_array($_func_name, $rules)) {
                    $result = isset($row[$_func_name])? $row[$_func_name] : null;
                } else {
                    $result = call_user_func($this->parseRules()[$_func_name], $row);
                }
                $this->rows[$key][$_func_name] = $result;
            }

            if ($return_mode == self::RETURN_MODE_WITH_KEYS) {
                $keys_to_unset = array_diff(array_keys($row), $rules_to_parse);
                foreach ($keys_to_unset as $key_to_unset) {
                    unset($this->rows[$key][$key_to_unset]);
                }
            }
        }

        if ($this->row_mode == self::ROW_MODE_SINGLE && isset($this->rows[0])) {
            return $this->rows[0];
        } else {
            return $this->rows;
        }
    }

    /**
     * 整理数据
     * @param string $method
     * @return array
     */
    final public function parseRows($method = 'parseSingleRow')
    {
        $this->beforeParseRows();
        if (!method_exists($this, $method)) {
            return $this->rows;
        } else {
            $this->rows = array_map([$this, $method], $this->rows);
            if ($this->row_mode == self::ROW_MODE_SINGLE && isset($this->rows[0])) {
                return $this->rows[0];
            } else {
                return $this->rows;
            }
        }
    }

    /**
     * 整理数居前的回调
     * @return mixed
     */
    protected function beforeParseRows()
    {
        return $this->rows;
    }

    /**
     * 获取值
     * @param $row
     * @param $key
     * @param null $default
     * @param string $trans
     * @return null
     */
    final protected function getValue($row, $key, $default = null, $trans = '')
    {
        if (isset($row[$key])) {
            $value = $row[$key];
        } else {
            $value = $default;
        }

        if (in_array($trans, ['intval', 'strval', 'floatval'])) {
            $value = $trans($value);
        }
        return $value;
    }

    /**
     * 递归地获取hash-table中的值
     * @param $row
     * @param string $key
     * @param null $default
     * @param string $trans
     * @return null
     */
    final protected function getValueRecursively($row, $key, $default = null, $trans = '')
    {
        $key_arr = explode('.', $key);
        if (count($key_arr) > 1) {
            $parent_key = $key_arr[0];
            if (isset($row[$parent_key])) {
                unset($key_arr[0]);
                $child_key = implode('.', $key_arr);
                return $this->getValueRecursively($row[$parent_key], $child_key, $default, $trans);
            } else {
                return $default;
            }
        } else {
            return $this->getValue($row, $key, $default, $trans);
        }
    }
}

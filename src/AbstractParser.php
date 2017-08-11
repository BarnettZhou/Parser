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

    protected $return_mode = self::RETURN_MODE_WITH_KEYS;

    /**
     * 所有数据
     * @var array
     */
    protected $rows = [];

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
     * 设置rows
     * @param $rows
     * @return $this
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    public function setParseMode($mode)
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
    public function setReturnMode($mode)
    {
        if (in_array($mode, [0, 1])) {
            $this->return_mode = $mode;
        }
        return $this;
    }

    /**
     * 根据给出的整理规则整理数据
     * @param array $keys
     * @return array
     */
    public function parseWithRules($keys = [])
    {
        $this->beforeParseRows();

        $parse_mode     = $this->parse_mode;
        $return_mode    = $this->return_mode;

        if ($parse_mode == self::PARSE_MODE_ONLY) {
            $rules_to_parse = array_intersect(array_keys($this->parseRules()), $keys);
        } else if ($parse_mode == self::PARSE_MODE_EXCEPT) {
            $rules_to_parse = array_diff(array_keys($this->parseRules()), $keys);
        } else {
            $rules_to_parse = array_keys($this->parseRules());
        }

        foreach ($this->rows as $key => $row) {
            foreach ($rules_to_parse as $_func_name) {
                $result = call_user_func($this->parseRules()[$_func_name], $row);
                $this->rows[$key][$_func_name] = $result;
            }

            if ($return_mode == self::RETURN_MODE_WITH_KEYS) {
                $keys_to_unset = array_diff(array_keys($row), $rules_to_parse);
                foreach ($keys_to_unset as $key_to_unset) {
                    unset($this->rows[$key][$key_to_unset]);
                }
            }
        }

        return $this->rows;
    }

    final public function parseRows()
    {
        $this->beforeParseRows();
        return array_map([$this, 'parseSingleRow'], $this->rows);
    }

    /**
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
    protected function getValue($row, $key, $default = null, $trans = '')
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
}
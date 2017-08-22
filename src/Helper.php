<?php

namespace Xuchen\Parser;

/**
 * 一些辅助函数
 * Trait Helper
 * @package Xuchen\Parser
 */

trait Helper
{
    /**
     * 隐藏手机号中间四位
     * @param $mobile
     * @param int $start
     * @param int $end
     * @return string
     */
    public function hideMobile($mobile, $start = 3, $end = 7)
    {
        $mobile_left    = substr($mobile, 0, $start);
        $mobile_right   = substr($mobile, $end, 10);
        $star_number    = $end - $start;

        return str_pad($mobile_left, $start + $star_number,
                '*', STR_PAD_RIGHT) . $mobile_right;
    }

    /**
     * 隐藏姓名
     * @param $name
     * @param string $mode center|left
     * @return string
     */
    public function hideName($name, $mode = 'center')
    {
        $mode = strtolower($mode);
        $name_length = mb_strlen($name);
        if ($mode == 'center') {
            $name_left  = mb_substr($name, 0, 1);
            if ($name_length > 2) {
                $name_right = mb_substr($name, $name_length - 1, 1);
                return $name_left . str_pad('', $name_length - 2, '*') . $name_right;
            } else {
                return $name_left . '*';
            }
        } else {
            $name_right = mb_substr($name, 1, $name_length - 1);
            return '*' . $name_right;
        }
    }
}
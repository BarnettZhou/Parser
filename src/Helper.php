<?php

namespace Xuchen\Parser;

/**
 * 一些辅助函数
 * Trait Helper
 * @package Xuchen\Parser
 */

trait Helper
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new ParserHelper();
    }
}

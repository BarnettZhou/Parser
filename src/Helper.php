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

    public function __construct($rows)
    {
        parent::__construct($rows);
        $this->helper = new ParserHelper();
    }
}

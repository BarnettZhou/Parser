<?php

namespace Xuchen\Parser;


class Parser extends AbstractParser
{
    public function __construct($rows = [])
    {
        if ($rows) {
            $this->setRows($rows);
        }
    }

    public function parseSingleRow(array $row)
    {
        return $row;
    }

    protected function parseRules()
    {
        return [];
    }
}

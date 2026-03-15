<?php

namespace ReviewX;

use ReviewX\Nahid\QArray\QueryEngine;
use ReviewX\Nahid\QArray\ArrayQuery;
if (!\function_exists('ReviewX\\convert_to_array')) {
    function convert_to_array($data)
    {
        return \ReviewX\Nahid\QArray\Utilities::toArray($data);
    }
}
if (!\function_exists('ReviewX\\qarray')) {
    /**
     * @param $data
     * @return \Nahid\QArray\QueryEngine
     */
    function qarray($data = [])
    {
        return \ReviewX\Nahid\QArray\Utilities::qarray($data);
    }
}

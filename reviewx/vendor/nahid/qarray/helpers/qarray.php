<?php

namespace Rvx;

use Rvx\Nahid\QArray\QueryEngine;
use Rvx\Nahid\QArray\ArrayQuery;
if (!\function_exists('Rvx\\convert_to_array')) {
    function convert_to_array($data)
    {
        return \Rvx\Nahid\QArray\Utilities::toArray($data);
    }
}
if (!\function_exists('Rvx\\qarray')) {
    /**
     * @param $data
     * @return \Nahid\QArray\QueryEngine
     */
    function qarray($data = [])
    {
        return \Rvx\Nahid\QArray\Utilities::qarray($data);
    }
}

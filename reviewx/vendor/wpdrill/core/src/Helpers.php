<?php

namespace ReviewX\WPDrill;

use ReviewX\WPDrill\Response;
class Helpers
{
    public static function rest(array $data) : Response
    {
        return new Response($data);
    }
    public static function path(array $segment) : string
    {
        return \implode(\DIRECTORY_SEPARATOR, $segment);
    }
}

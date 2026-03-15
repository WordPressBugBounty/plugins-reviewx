<?php

namespace ReviewX\WPDrill\Facades;

use ReviewX\WPDrill\Facade;
/**
 * @method static string add(string $code, $handler)
 */
class Shortcode extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'shortcode';
    }
}

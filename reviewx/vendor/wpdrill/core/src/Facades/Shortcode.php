<?php

namespace Rvx\WPDrill\Facades;

use Rvx\WPDrill\Facade;
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

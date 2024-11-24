<?php

namespace Rvx\WPDrill\Facades;

use Rvx\WPDrill\Facade;
use Rvx\WPDrill\Menus\Menu as MenuOption;
/**
 * @method static MenuOption add(string $pageTitle, $handler, string $capability)
 * @method static void remove(string $slug, ?string $submenuSlug = null)
 * @method static MenuOption group(string $pageTitle, $handler, string $capability, callable $fn)
 */
class Menu extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'menu';
    }
}

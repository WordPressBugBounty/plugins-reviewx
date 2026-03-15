<?php

namespace ReviewX\Utilities\Auth;

\defined("ABSPATH") || exit;
use ReviewX\WPDrill\Facade;
/**
 * Class WpUser
 *
 * @method static void setLoggedInStatus(bool $value)
 * @method static void setAbility(bool $value)
 * @method static bool isLoggedIn()
 * @method static bool can()
 */
class WpUser extends Facade
{
    public static function getFacadeAccessor() : string
    {
        return \ReviewX\Utilities\Auth\WpUserManager::class;
    }
}

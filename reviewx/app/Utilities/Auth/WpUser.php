<?php

namespace Rvx\Utilities\Auth;

use Rvx\WPDrill\Facade;
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
        return \Rvx\Utilities\Auth\WpUserManager::class;
    }
}

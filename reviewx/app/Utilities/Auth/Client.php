<?php

namespace Rvx\Utilities\Auth;

use Rvx\WPDrill\Facade;
/**
 * Class Client
 *
 * @method static bool has()
 * @method static object|null site()
 * @method static string getUid()
 * @method static int getSiteId()
 * @method static string getName()
 * @method static string getDomain()
 * @method static string getUrl()
 * @method static bool getSync()
 * @method static string getSecret()
 * @package Rvx\Utilities\Auth
 */
class Client extends Facade
{
    public static function getFacadeAccessor() : string
    {
        return \Rvx\Utilities\Auth\ClientManager::class;
    }
}

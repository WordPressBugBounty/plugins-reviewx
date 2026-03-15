<?php

namespace ReviewX\Utilities\Auth;

\defined("ABSPATH") || exit;
use ReviewX\WPDrill\Facade;
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
 * @package ReviewX\Utilities\Auth
 */
class Client extends Facade
{
    public static function getFacadeAccessor() : string
    {
        return \ReviewX\Utilities\Auth\ClientManager::class;
    }
}

<?php

namespace ReviewX\WPDrill\Facades;

use ReviewX\WPDrill\Facade;
use ReviewX\WPDrill\ConfigManager;
use ReviewX\Noodlehaus\ConfigInterface;
/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void set(string $key, mixed $value)
 * @method static bool has(string $key)
 * @method static ConfigInterface merge(ConfigInterface $config)
 * @method static array all()
 */
class Config extends Facade
{
    public static function getFacadeAccessor()
    {
        return ConfigManager::class;
    }
}

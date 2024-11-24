<?php

namespace Rvx\Handlers;

class HookManager
{
    protected static $action;
    protected static $imported_products = [];
    public static function setAction($value)
    {
        self::$action = $value;
    }
    public static function getAction()
    {
        return self::$action;
    }
    public static function addImportedProduct($product)
    {
        self::$imported_products[] = $product;
    }
    public static function getImportedProducts()
    {
        return self::$imported_products;
    }
    public static function clearImportedProducts()
    {
        self::$imported_products = [];
    }
}

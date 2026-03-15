<?php

namespace ReviewX\WPDrill\Views;

use ReviewX\Twig\Extension\AbstractExtension;
use ReviewX\WPDrill\Facades\Config;
class TwigFunctions extends AbstractExtension
{
    public function getFunctions()
    {
        $functions = Config::get('view.functions', []);
        $twigFuncs = [];
        foreach ($functions as $name => $function) {
            $twigFuncs[] = new \ReviewX\Twig\TwigFunction($name, $function);
        }
        return $twigFuncs;
    }
}

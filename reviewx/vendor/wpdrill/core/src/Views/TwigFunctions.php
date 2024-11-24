<?php

namespace Rvx\WPDrill\Views;

use Rvx\Twig\Extension\AbstractExtension;
use Rvx\WPDrill\Facades\Config;
class TwigFunctions extends AbstractExtension
{
    public function getFunctions()
    {
        $functions = Config::get('view.functions', []);
        $twigFuncs = [];
        foreach ($functions as $name => $function) {
            $twigFuncs[] = new \Rvx\Twig\TwigFunction($name, $function);
        }
        return $twigFuncs;
    }
}

<?php

namespace Rvx\WPDrill;

abstract class ServiceProvider
{
    protected Plugin $plugin;
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
    public abstract function register() : void;
    public abstract function boot() : void;
}

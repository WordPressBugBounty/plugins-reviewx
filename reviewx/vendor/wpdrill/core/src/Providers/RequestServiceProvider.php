<?php

namespace ReviewX\WPDrill\Providers;

use ReviewX\WPDrill\ConfigManager;
use ReviewX\WPDrill\Routing\RouteManager;
use ReviewX\WPDrill\ServiceProvider;
use ReviewX\Psr\Http\Message\ServerRequestInterface;
class RequestServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(ServerRequestInterface::class, function () {
            $psr17Factory = new \ReviewX\Nyholm\Psr7\Factory\Psr17Factory();
            $creator = new \ReviewX\Nyholm\Psr7Server\ServerRequestCreator(
                $psr17Factory,
                // ServerRequestFactory
                $psr17Factory,
                // UriFactory
                $psr17Factory,
                // UploadedFileFactory
                $psr17Factory
            );
            return $creator->fromGlobals();
        });
    }
    public function boot() : void
    {
    }
}

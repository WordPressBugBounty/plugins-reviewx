<?php

namespace Rvx\WPDrill\Providers;

use Rvx\WPDrill\ConfigManager;
use Rvx\WPDrill\Routing\RouteManager;
use Rvx\WPDrill\ServiceProvider;
use Rvx\Psr\Http\Message\ServerRequestInterface;
class RequestServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(ServerRequestInterface::class, function () {
            $psr17Factory = new \Rvx\Nyholm\Psr7\Factory\Psr17Factory();
            $creator = new \Rvx\Nyholm\Psr7Server\ServerRequestCreator(
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

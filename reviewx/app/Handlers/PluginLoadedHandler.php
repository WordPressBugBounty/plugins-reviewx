<?php

namespace Rvx\Handlers;

use Rvx\WPDrill\Contracts\InvokableContract;
class PluginLoadedHandler implements InvokableContract
{
    public function __invoke()
    {
        //file_put_contents(REVIEWX_DIR_PATH . 'plugin.txt', 'Plugin Deactivated 1!');
    }
}

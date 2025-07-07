<?php

namespace Rvx\Handlers\RvxInit;

class LoadTextDomainHandler
{
    /**
     * Load the plugin text domain for translations.
     *
     * This method loads the text domain for the ReviewX plugin, allowing it to be translated.
     */
    public function loadLanguage()
    {
        load_plugin_textdomain('reviewx', \false, \dirname(plugin_basename(__DIR__)) . '/languages');
    }
}

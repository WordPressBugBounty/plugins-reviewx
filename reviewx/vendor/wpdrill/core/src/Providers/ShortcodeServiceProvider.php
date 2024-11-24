<?php

namespace Rvx\WPDrill\Providers;

use Rvx\WPDrill\ServiceProvider;
use Rvx\WPDrill\Shortcodes\ShortcodeManager;
class ShortcodeServiceProvider extends ServiceProvider
{
    protected ShortcodeManager $shortcode;
    public function register() : void
    {
        $this->shortcode = new ShortcodeManager($this->plugin);
        $this->plugin->bind('shortcode', function () {
            return $this->shortcode;
        });
    }
    public function boot() : void
    {
        add_action('init', function () {
            $shortcode = (require $this->plugin->getPath('bootstrap/shortcodes.php'));
            $shortcode($this->plugin);
            $this->shortcode->register();
        });
    }
}

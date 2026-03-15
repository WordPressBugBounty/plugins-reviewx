<?php

namespace ReviewX\WPDrill\Shortcodes;

use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Contracts\ShortcodeContract;
use ReviewX\WPDrill\Menus\Menu;
use ReviewX\WPDrill\Plugin;
class ShortcodeManager
{
    protected Plugin $plugin;
    protected array $shortcodes = [];
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
    public function add(string $code, $handler) : self
    {
        if (\is_string($handler) && \class_exists($handler)) {
            $handler = $this->plugin->resolve($handler);
        }
        if (!$handler instanceof ShortcodeContract) {
            throw new \Exception('Handler must be an instance of ShortcodeContract');
        }
        $handler = [$handler, 'render'];
        $this->shortcodes[$code] = $handler;
        return $this;
    }
    public function register()
    {
        foreach ($this->shortcodes as $code => $handler) {
            add_shortcode($code, $handler);
        }
    }
}

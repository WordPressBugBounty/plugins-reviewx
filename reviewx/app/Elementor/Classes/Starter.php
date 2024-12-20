<?php

namespace Rvx\Elementor\Classes;

use Rvx\Elementor\Traits\Addons;
use Rvx\Elementor\Traits\Library;
if (!\defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
/**
 * Class Starter
 * @package Rvx\Elementor\Classes
 */
class Starter
{
    use Addons, Library;
    // instance container
    private static $instance = null;
    // registered elements container
    public $registered_elements;
    // registered extensions container
    public $registered_extensions;
    // additional settings
    public $additional_settings;
    /**
     * Singleton instance
     *
     * @since 3.0.0
     */
    public static function instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * Constructor of plugin class
     *
     * @since 3.0.0
     */
    private function __construct()
    {
        // elements classmap
        $this->registered_elements = ['rxcall-to-review' => ['class' => 'Rvx\\Elementor\\Elements\\Data_Tabs'], 'rxcall-to-review-widget' => ['class' => 'Rvx\\Elementor\\Elements\\Review_Widget']];
        // extensions classmap
        //        $this->registered_extensions = apply_filters('rx/registered_extensions', Helper::arrayGet(config('settings'), 'rxextensions'));
        //
        //        // additional settings
        $this->additional_settings = apply_filters('rx/additional_settings', ['quick_tools' => \true]);
        // register hooks
        $this->register_hooks();
    }
    /**
     * Register Hooks
     *
     * @return void
     */
    protected function register_hooks()
    {
        // Elements
        add_action('elementor/elements/categories_registered', array($this, 'register_widget_categories'));
        add_action('elementor/widgets/widgets_registered', array($this, 'register_elements'));
        add_action('elementor/widgets/register', array($this, 'cpt_register_element'));
    }
}

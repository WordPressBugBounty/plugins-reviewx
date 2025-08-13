<?php

namespace Rvx\RvxDivi;

use Rvx\RvxDivi\includes\RvxReviews;
use Rvx\RvxDivi\includes\RvxTabs;
class RvxDivi
{
    public function __construct()
    {
        add_action('divi_extensions_init', [$this, 'initialize']);
    }
    /**
     * Initializes the extension by loading necessary files.
     *
     * @since 1.0.0
     */
    public function initialize()
    {
        if (\class_exists('WooCommerce')) {
            $this->loadFiles();
        }
    }
    /**
     * Load the required files for the extension.
     *
     * @since 1.0.0
     */
    private function loadFiles()
    {
        new RvxTabs();
        new RvxReviews();
    }
}

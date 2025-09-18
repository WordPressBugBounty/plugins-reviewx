<?php

namespace Rvx\Form;

use Rvx\CPT\CptHelper;
use Rvx\Utilities\Auth\Client;
class ReviewForm
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    public static function post_type_support()
    {
        if (Client::getSync()) {
            // Retrieve settings
            $enabled_post_types = (new self())->cptHelper->enabledCPT();
            // Dynamically add comment support for enabled post types
            foreach ($enabled_post_types as $post_type) {
                if (post_type_exists($post_type)) {
                    add_post_type_support($post_type, 'comments');
                }
            }
        }
    }
    public static function comments_template_init($default)
    {
        if (Client::getSync()) {
            // Retrieve settings
            $enabled_post_types = (new self())->cptHelper->enabledCPT();
            // Check if the current post type is enabled
            if (is_singular($enabled_post_types)) {
                // Exclude WooCommerce account page
                if (\class_exists('WooCommerce') && is_account_page()) {
                    // Do nothing on WooCommerce front-end user dashboard
                } else {
                    // Invoke custom handler
                    if (\class_exists('Rvx\\Handlers\\CommentBoxHandle')) {
                        (new \Rvx\Handlers\CommentBoxHandle())->__invoke();
                    }
                    // Load custom template if it exists
                    $custom_template = \dirname(__FILE__) . '/widget.php';
                    if (\file_exists($custom_template)) {
                        return $custom_template;
                    }
                }
            }
            // Fallback to default template
            return $default;
        }
    }
}

<?php

namespace Rvx\Handlers;

class CommentStatusHandler
{
    public function wooProductSaveHandler()
    {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(\sanitize_text_field(\wp_unslash($_POST['_wpnonce'])), 'woocommerce-settings')) {
            return;
        }
        // Isset specific fields
    }
}

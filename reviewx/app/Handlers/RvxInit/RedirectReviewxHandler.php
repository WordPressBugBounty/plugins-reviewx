<?php

namespace Rvx\Handlers\RvxInit;

class RedirectReviewxHandler
{
    public function __invoke($plugin)
    {
        if ('reviewx/reviewx.php' === $plugin) {
            wp_safe_redirect(admin_url() . 'admin.php?page=reviewx');
            exit;
        }
    }
}

<?php

namespace Rvx\Handlers\RvxInit;

class RedirectReviewxHandler
{
    public function __invoke($plugin)
    {
        if (RVX_DIR_NAME . '/reviewx.php' === $plugin) {
            wp_safe_redirect(admin_url() . 'admin.php?page=reviewx');
            exit;
        }
    }
}

<?php

namespace Rvx\Handlers;

class IsAlreadySyncSucess
{
    public function __construct()
    {
        add_action('admin_footer', function () {
            if (get_option('rvx_reset_sync_flag')) {
                ?>
                <script>
                    localStorage.setItem('isAlreadySyncSuccess', 'false');
                </script>
                <?php 
                delete_option('rvx_reset_sync_flag');
            }
        });
    }
}

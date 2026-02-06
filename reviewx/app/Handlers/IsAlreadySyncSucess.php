<?php

namespace Rvx\Handlers;

class IsAlreadySyncSucess
{
    public function resetSyncFlag()
    {
        add_action('admin_footer', function () {
            if (!\get_transient('rvx_reset_sync_flag')) {
                return;
            }
            ?>
            <script>
                try {
                    localStorage.setItem('isAlreadySyncSuccess', 'false');
                } catch (e) {
                    console.warn('ReviewX: Unable to access localStorage');
                }
            </script>
            <?php 
            \delete_transient('rvx_reset_sync_flag');
        });
    }
}

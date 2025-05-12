<?php

namespace Rvx\Handlers\MigrationRollback;

use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Facades\View;
class RollbackMenuHandler implements InvokableContract
{
    public function __invoke()
    {
        $sharedMethods = new \Rvx\Handlers\MigrationRollback\SharedMethods();
        $rollbackPrompt = new \Rvx\Handlers\MigrationRollback\RollbackPrompt();
        echo '<div class="wrap p-6 bg-gray-100 rounded-lg shadow-md">';
        View::output('storeadmin/rollback', ['title' => 'Rollback to ReviewX v1', 'content' => 'Revert to the previous version with ease and restore functionality seamlessly.', 'page_url' => $_SERVER['REQUEST_URI']]);
        if (isset($_GET['rollback_start'])) {
            $is_pro_active = $sharedMethods->rvx_is_old_pro_plugin_active();
            if ($is_pro_active === \false) {
                //$sharedMethods->rvx_activate_old_pro_plugin(); // Activate v1 Pro
            }
            $rollbackPrompt->rvx_retrieve_sass_plugin_reviews_meta_updater();
            // Start data rollback
            echo <<<HTML
<div class="p-4 bg-white rounded-lg shadow-md">
    <h2 class="text-lg font-bold text-gray-800 mb-2">Your data has been successfully rolled back to the previous version.</h2>
    <p class="text-gray-600">Thank you for your patience!</p>
    
    <h3 class="text-md font-semibold text-gray-800 mt-4">Next Steps:</h3>
    <ul class="list-disc list-inside text-gray-600">
        <li><strong>Must do:</strong> Please make sure to <b>delete ReviewX v2</b> and and <b>reinstall ReviewX v1</b> plugin.</li>
        <li><strong>Pro user:</strong> If you used the Pro version, please it activate it to access the premium features.</li>
        <li><strong>Download:</strong> ReviewX v1 plugin is available here, <a target="_blank" href="https://downloads.wordpress.org/plugin/reviewx.1.6.30.zip" class="text-blue-600 hover:underline">Download v1</a>.</li>
    </ul>

    <h3 class="text-md font-semibold text-gray-800 mt-4">Need Assistance?</h3>
    <p class="text-gray-600">
        Our support team is here to help. 
        <a target="_blank" href="https://reviewx.io/support/" class="text-blue-600 hover:underline">Contact Support</a> 
        or email us at <a href="mailto:support@reviewx.io" class="text-blue-600 hover:underline">support@reviewx.io</a>.
    </p>
</div>
HTML;
        }
        // Remove the query parameter without reloading
        echo <<<JS
<script>
document.addEventListener("DOMContentLoaded", function() {
    const url = new URL(window.location.href);
    url.searchParams.delete("rollback_start");
    history.replaceState(null, "", url);
});
</script>
JS;
        echo '</div>';
    }
}

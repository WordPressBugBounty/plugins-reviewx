<?php

namespace Rvx\Handlers;

use Rvx\Api\AuthApi;
use Rvx\CPT\CptHelper;
use Rvx\Services\Api\LoginService;
use Rvx\Services\DataSyncService;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\DB\Migration\Migrator;
use Rvx\Services\CacheServices;
class PluginActivatedHandler implements InvokableContract
{
    private Migrator $migrator;
    private DataSyncService $dataSyncService;
    private CacheServices $cacheServices;
    public function __construct(Migrator $migrator)
    {
        $this->migrator = $migrator;
        $this->dataSyncService = new DataSyncService();
        $this->cacheServices = new CacheServices();
    }
    public function __invoke()
    {
        $this->migrator->run();
        global $wpdb;
        $rvxSites = $wpdb->prefix . 'rvx_sites';
        $uid = $wpdb->get_var("SELECT uid FROM {$rvxSites} ORDER BY id DESC LIMIT 1");
        if ($uid) {
            // Change rvx_sites table is_saas_sync to 0
            $wpdb->update(
                $rvxSites,
                ['is_saas_sync' => 0],
                ['uid' => $uid],
                ['%d'],
                // format for is_saas_sync (integer)
                ['%s']
            );
            // Set the localStorage isAlreadySyncSuccess to false
            update_option('rvx_reset_sync_flag', \true);
            (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 1, 'plugin_version' => RVX_VERSION, 'wp_version' => get_bloginfo('version')]);
            $dataResponse = $this->dataSyncService->dataSync('login', 'product');
            if (!$dataResponse) {
                return Helper::rvxApi(['error' => 'Data sync fails'])->fails('Data sync fails', $dataResponse->getStatusCode());
            }
            // Sleep for 1 seconds
            \sleep(1);
            // Upload CPT data to Saas
            $enabled_post_types = (new CptHelper())->usedCPTOnSync('used');
            unset($enabled_post_types['product']);
            // Exclude 'product' post type
            // Loop through each post type and hook into the actions/filters dynamically
            foreach ($enabled_post_types as $post_type) {
                $this->dataSyncService->dataSync('login', $post_type);
            }
            $this->cacheServices->removeCache();
            (new LoginService())->resetPostMeta();
        }
    }
}

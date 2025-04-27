<?php

namespace Rvx\Handlers;

use Rvx\Api\AuthApi;
use Rvx\Utilities\Helper;
use Rvx\Services\DataSyncService;
use Rvx\Services\Api\LoginService;
use Rvx\WPDrill\DB\Migration\Migrator;
use Rvx\WPDrill\Contracts\InvokableContract;
class PluginActivatedHandler implements InvokableContract
{
    private Migrator $migrator;
    public function __construct(Migrator $migrator)
    {
        $this->migrator = $migrator;
    }
    public function __invoke()
    {
        $this->migrator->run();
        global $wpdb;
        $rvxSites = $wpdb->prefix . 'rvx_sites';
        $uid = $wpdb->get_var("SELECT uid FROM {$rvxSites} ORDER BY id DESC LIMIT 1");
        if ($uid) {
            (new AuthApi())->changePluginStatus(['site_uid' => $uid, 'status' => 1]);
            $dataResponse = (new DataSyncService())->dataSync('login');
            if (!$dataResponse) {
                return Helper::rvxApi(['error' => 'Data sync fails'])->fails('Data sync fails', $dataResponse->getStatusCode());
            }
            (new LoginService())->resetPostMeta();
        }
    }
}

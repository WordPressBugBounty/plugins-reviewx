<?php

namespace Rvx\Handlers\MigrationRollback;

use Rvx\Rest\Controllers\DataSyncController;
use Rvx\Utilities\Auth\Client;
class UpgradeDBSettings
{
    protected $dataSyncController;
    // Option name used as a flag to indicate the upgrade has run.
    public function __construct()
    {
        $this->dataSyncController = new DataSyncController();
        $this->run_upgrade();
    }
    /**
     * Run the upgrade routine if it hasn't already been executed.
     */
    public function run_upgrade()
    {
        if (!Client::getSync()) {
            return;
        }
        if (get_option('_rvx_db_upgrade_216', \false) === \true) {
            return;
        }
        // Retrieve the current settings.
        $product_settings = get_option('_rvx_settings_product');
        $widget_settings = get_option('_rvx_settings_widget');
        $cpt_settings = get_option('_rvx_cpt_settings');
        // If any of the required options are missing, run upgrade logic.
        if (\false === $product_settings || \false === $widget_settings || \false === $cpt_settings) {
            // Initialize default settings if they are not available.
            $this->dataSyncController->updateSettingsOnSync();
        }
        // Mark the upgrade routine as completed so it doesn't run again.
        update_option('_rvx_db_upgrade_216', \true);
    }
}

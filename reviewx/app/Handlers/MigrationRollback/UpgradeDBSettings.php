<?php

namespace ReviewX\Handlers\MigrationRollback;

use ReviewX\Rest\Controllers\DataSyncController;
use ReviewX\Utilities\Auth\Client;
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
        // Check if the rvx_sites table exists before querying it.
        // On fresh installs, the migration may not have run yet.
        global $wpdb;
        $table_name = $wpdb->prefix . 'rvx_sites';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
        if (!$table_exists) {
            return;
        }
        if (!Client::getSync()) {
            return;
        }
        if (\get_option('_rvx_db_upgrade_216', \false) === \true) {
            return;
        }
        // Retrieve the current settings.
        $product_settings = \get_option('_rvx_settings_product');
        $widget_settings = \get_option('_rvx_settings_widget');
        $cpt_settings = \get_option('_rvx_cpt_settings');
        // If any of the required options are missing, run upgrade logic.
        if (\false === $product_settings || \false === $widget_settings || \false === $cpt_settings) {
            // Initialize default settings if they are not available.
            $this->dataSyncController->updateSettingsOnSync();
        }
        // Mark the upgrade routine as completed so it doesn't run again.
        \update_option('_rvx_db_upgrade_216', \true);
    }
}

<?php

namespace Rvx\Handlers\RvxInit;

class LoadReviewxCreateSiteTable
{
    public function __invoke()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rvx_sites';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE {$table_name} (\n                id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,\n                name VARCHAR(255) NOT NULL,\n                site_id INT(11) NOT NULL,\n                uid VARCHAR(32) NOT NULL,\n                domain VARCHAR(255) NOT NULL,\n                url VARCHAR(255) NOT NULL,\n                locale CHAR(10) NOT NULL,\n                email VARCHAR(100) NOT NULL,\n                secret VARCHAR(100) NOT NULL,\n                is_saas_sync TINYINT(1) DEFAULT 0,\n                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n                PRIMARY KEY (id)\n            ) {$charset_collate};";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }
}

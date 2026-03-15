<?php

namespace ReviewX\Handlers\ReviewXInit;

class LoadReviewxCreateSiteTable
{
    /**
     * Invokes the process to ensure the table exists.
     */
    public function init()
    {
        if (!$this->is_table_exists()) {
            $this->create_table();
        }
    }
    /**
     * Checks if the table exists in the database.
     *
     * @return bool True if the table exists, false otherwise.
     */
    private function is_table_exists()
    {
        $table_name = $this->get_table_name();
        $cache_key = 'rvx_table_exists_' . $table_name;
        $cached = \wp_cache_get($cache_key, 'reviewx');
        if (\false !== $cached) {
            return (bool) $cached;
        }
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table schema check
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
        \wp_cache_set($cache_key, (int) $exists, 'reviewx', 86400);
        // 1 day
        return $exists;
    }
    /**
     * Creates the database table if it does not exist.
     */
    private function create_table()
    {
        global $wpdb;
        $table_name = $this->get_table_name();
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$table_name} (\n            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,\n            name VARCHAR(255) NOT NULL,\n            site_id INT(11) NOT NULL,\n            uid VARCHAR(32) NOT NULL,\n            domain VARCHAR(255) NOT NULL,\n            url VARCHAR(255) NOT NULL,\n            locale CHAR(10) NOT NULL,\n            email VARCHAR(100) NOT NULL,\n            secret VARCHAR(100) NOT NULL,\n            is_saas_sync TINYINT(1) DEFAULT 0,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            PRIMARY KEY (id)\n        ) {$charset_collate};";
        require_once \ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    /**
     * Returns the table name.
     *
     * @return string Fully qualified table name.
     */
    private function get_table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'rvx_sites';
    }
}

<?php

namespace Rvx;

use Rvx\WPDrill\DB\Migration\Migration;
use Rvx\WPDrill\DB\Migration\Sql;
class create_sites_table extends Migration
{
    public function up() : Sql
    {
        return $this->createTable('sites', [
            'id' => 'INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
            'name' => 'VARCHAR(255) NOT NULL',
            'site_id' => 'INT(11) NOT NULL',
            'uid' => 'VARCHAR(32) NOT NULL',
            'domain' => 'VARCHAR(255) NOT NULL',
            'url' => 'VARCHAR(255) NOT NULL',
            'locale' => 'CHAR(10) NOT NULL',
            'email' => 'VARCHAR(100) NOT NULL',
            'secret' => 'VARCHAR(100) NOT NULL',
            'is_saas_sync' => 'TINYINT(1) DEFAULT 0',
            // Corrected syntax
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'DATETIME NULL',
        ]);
    }
    public function down() : Sql
    {
        return $this->dropTable('sites');
    }
}
\class_alias('Rvx\\create_sites_table', 'create_sites_table', \false);

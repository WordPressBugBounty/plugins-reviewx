<?php

namespace ReviewX\WPDrill\Contracts;

use ReviewX\WPDrill\DB\Migration\Sql;
use ReviewX\WPDrill\Plugin;
interface MigrationContract
{
    public function up() : Sql;
    public function down() : Sql;
}

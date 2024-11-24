<?php

namespace Rvx\WPDrill\Contracts;

use Rvx\WPDrill\DB\Migration\Sql;
use Rvx\WPDrill\Plugin;
interface MigrationContract
{
    public function up() : Sql;
    public function down() : Sql;
}

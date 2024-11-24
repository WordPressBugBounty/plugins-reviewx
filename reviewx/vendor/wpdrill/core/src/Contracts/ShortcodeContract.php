<?php

namespace Rvx\WPDrill\Contracts;

use Rvx\WPDrill\DB\Migration\Sql;
use Rvx\WPDrill\Plugin;
interface ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string;
}

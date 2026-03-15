<?php

namespace ReviewX\WPDrill\Contracts;

use ReviewX\WPDrill\DB\Migration\Sql;
use ReviewX\WPDrill\Plugin;
interface ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string;
}

<?php

namespace ReviewX\Models;

\defined("ABSPATH") || exit;
use ReviewX\WPDrill\Models\Model;
class Category extends Model
{
    protected static $table = 'terms';
}

<?php

namespace Rvx\Handlers\WcTemplates;

class WcAccountFormTag
{
    public function __invoke()
    {
        echo 'enctype="multipart/form-data"';
    }
}

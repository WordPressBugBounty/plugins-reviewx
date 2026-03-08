<?php

namespace Rvx\Handlers\WcTemplates;

class WcAccountFormTag
{
    public function __invoke()
    {
        \printf(' %s ', \esc_attr('enctype="multipart/form-data"'));
    }
}

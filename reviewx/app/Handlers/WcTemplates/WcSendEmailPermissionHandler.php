<?php

namespace Rvx\Handlers\WcTemplates;

class WcSendEmailPermissionHandler
{
    public function __invoke($fields)
    {
        $fields['order']['consent_email_subscription'] = ['type' => 'checkbox', 'label' => __('I want to subscribe email', 'reviewx')];
        return $fields;
    }
}

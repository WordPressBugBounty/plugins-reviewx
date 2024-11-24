<?php

namespace Rvx\Handlers\WcTemplates;

use Rvx\Api\UserApi;
use Rvx\Utilities\Auth\Client;
class WcSendEmailPermissionHandler
{
    public function __invoke($fields)
    {
        $fields['order']['consent_email_subscription'] = ['type' => 'checkbox', 'label' => __('I want to subscribe email', 'reviewx')];
        return $fields;
    }
}

<?php

namespace ReviewX\Handlers;

use ReviewX\Api\UserApi;
use ReviewX\Utilities\Auth\Client;
use ReviewX\WPDrill\Response;
class UserDeleteHandler
{
    public function __construct()
    {
    }
    public function __invoke($user_id)
    {
        $id = Client::getUid() . '-' . $user_id;
        $response = (new UserApi())->remove($id);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return \false;
        }
    }
}

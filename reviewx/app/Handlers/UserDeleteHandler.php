<?php

namespace Rvx\Handlers;

use Rvx\Api\UserApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
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

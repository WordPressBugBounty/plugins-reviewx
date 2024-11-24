<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\UserServices;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
class UserController implements InvokableContract
{
    protected UserServices $userServices;
    /**
     *
     */
    public function __construct()
    {
        $this->userServices = new UserServices();
    }
    /**
     * @return void
     */
    public function __invoke()
    {
    }
    /**
     * @return Response
     */
    public function getUser()
    {
        return $this->userServices->getUser();
    }
}

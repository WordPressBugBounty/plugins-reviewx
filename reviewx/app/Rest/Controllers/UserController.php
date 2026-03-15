<?php

namespace ReviewX\Rest\Controllers;

\defined("ABSPATH") || exit;
use ReviewX\Services\UserServices;
use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Response;
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

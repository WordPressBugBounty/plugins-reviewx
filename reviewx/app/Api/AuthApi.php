<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
use Exception;
class AuthApi extends \Rvx\Api\BaseApi
{
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function login(array $data) : Response
    {
        return $this->withJson($data)->post('login');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function register(array $data) : Response
    {
        if ($data['domain'] === 'localhost') {
            $ip = (new \Rvx\Api\BaseApi())->getIp();
            $data['url'] = $ip;
        }
        return $this->withJson($data)->post('register');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function licenseLogin(array $data) : Response
    {
        return $this->withJson($data)->post('/login/key');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function forgetPassword(array $data) : Response
    {
        return $this->withJson($data)->post('forget-password');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function resetPassword(array $data) : Response
    {
        return $this->withJson($data)->post('reset-password');
    }
    public function changePluginStatus(array $data) : Response
    {
        return $this->withJson($data)->post('/plugin/change-status');
    }
}

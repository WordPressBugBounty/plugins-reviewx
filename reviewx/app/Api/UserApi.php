<?php

namespace ReviewX\Api;

use ReviewX\Apiz\Http\Response;
class UserApi extends \ReviewX\Api\BaseApi
{
    /**
     * @return Response
     * @throws \Exception
     */
    public function getUser() : Response
    {
        return $this->get('customers');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function create(array $data) : Response
    {
        return $this->withJson($data)->post('customer');
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function update(array $data, $uid) : Response
    {
        return $this->withJson($data)->put('customer/' . $uid . '/update');
    }
    public function editAccountUpdate(array $data, $uid) : Response
    {
        return $this->withJson($data)->put('customer/' . $uid . '/update');
    }
    /**
     * @param $id
     * @return Response
     * @throws Exception
     */
    public function remove($id) : Response
    {
        return $this->delete('customer/' . $id);
    }
}

<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
use Exception;
class OrderApi extends \Rvx\Api\BaseApi
{
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function create(array $data) : Response
    {
        return $this->withJson($data)->post('orders/store');
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function update(array $data, $uid) : Response
    {
        return $this->withJson($data)->put('orders/' . $uid . '/update');
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function changeStatus(array $data, $uid) : Response
    {
        return $this->withJson($data)->put('orders/' . $uid . '/change/status');
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function changeBulkStatus(array $data) : Response
    {
        return $this->withJson($data)->post('orders/bulk/change/status');
    }
    /**
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function delete($uid) : Response
    {
        return $this->delete('orders/' . $uid);
    }
}

<?php

namespace ReviewX\Api;

use ReviewX\Apiz\Http\Response;
class CategoryApi extends \ReviewX\Api\BaseApi
{
    /**
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function selectable() : Response
    {
        return $this->get('category/selectable');
    }
    public function getCategory() : Response
    {
        return $this->get('categories');
    }
    public function create(array $data)
    {
        return $this->withJson($data)->post('category');
    }
    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function remove($id)
    {
        return $this->delete('category/' . $id);
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws \Exception
     */
    public function update(array $data, $uid) : Response
    {
        return $this->withJson($data)->put('category/' . $uid . '/update');
    }
    public function dataSync(array $data)
    {
        return $this->withJson($data)->post('sync/categories');
    }
}

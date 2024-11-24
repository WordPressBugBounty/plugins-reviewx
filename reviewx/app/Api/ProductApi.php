<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
use Exception;
class ProductApi extends \Rvx\Api\BaseApi
{
    /**
     * @return Response
     * @throws Exception
     */
    public function getProductSelect($id) : Response
    {
        if (!empty($id['search'])) {
            return $this->get('products/selectable?search=' . $id['search']);
        }
        return $this->get('products/selectable');
    }
    /**
     * @return Response
     * @throws Exception
     */
    public function getProducts() : Response
    {
        return $this->get('products');
    }
    /**
     * @return Response
     * @throws Exception
     */
    public function getProductInsights() : Response
    {
        return $this->get('products/insights');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function create(array $data) : Response
    {
        return $this->withJson($data)->post('products/store');
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function update(array $data, $uid) : Response
    {
        return $this->withJson($data)->put('products/' . $uid . '/update');
    }
    /**
     * @param $uid
     * @param $status
     * @return Response
     * @throws Exception
     */
    public function status($uid, $status) : Response
    {
        return $this->put('products/' . $uid . '/status?status=' . $status);
    }
    /**
     * @param $id
     * @return mixed
     * @throws Exception
     */
    public function remove($id)
    {
        $link = 'products/' . $id;
        return $this->delete($link);
    }
    /**
     * @param $id
     * @return mixed
     * @throws Exception
     */
    public function trashToRestoreWpProduct($id)
    {
        $link = 'products/' . $id . '/restore';
        return $this->put($link);
    }
}

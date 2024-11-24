<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
class CustomPostApi extends \Rvx\Api\BaseApi
{
    /**
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function customGet() : Response
    {
        return $this->get('custom-post-type');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function customStore(array $data) : Response
    {
        return $this->withJson($data)->post('custom-post-type/store');
    }
    /**
     * @param $id
     * @return mixed
     * @throws Exception
     */
    public function customdelete($id)
    {
        return $this->delete('custom-post-type/' . $id['uid']);
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function customUpdate(array $data, $uid) : Response
    {
        $url = 'custom-post-type/' . $uid . '/update';
        return $this->withJson($data)->put($url);
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function customPostStatusChange(array $data, $uid) : Response
    {
        $url = 'custom-post-type/' . $uid . '/status-change';
        return $this->withJson($data)->put($url);
    }
}

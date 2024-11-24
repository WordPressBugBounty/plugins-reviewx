<?php

namespace Rvx\Services;

use Rvx\Apiz\Http\Response;
use Rvx\Api\CustomPostApi;
use Rvx\Utilities\Auth\Client;
class CustomService extends \Rvx\Services\Service
{
    /**
     *
     */
    public function __construct()
    {
        //        add_action('save_post', [$this, 'saveProduct'], 10, 1);
    }
    /**
     * @return Response
     */
    public function customGet()
    {
        return (new CustomPostApi())->customGet();
    }
    public function customStore($request)
    {
        $uid = ['wp_unique_id' => Client::getUid()];
        $data = \array_merge($request, $uid);
        return (new CustomPostApi())->customStore($data);
    }
    public function customUpdate($data)
    {
        $uid = $data['uid'];
        return (new CustomPostApi())->customUpdate($data, $uid);
    }
    public function customdelete($data)
    {
        return (new CustomPostApi())->customdelete($data);
    }
    public function customPostStatusChange($data)
    {
        $uid = $data['uid'];
        return (new CustomPostApi())->customPostStatusChange($data, $uid);
    }
}

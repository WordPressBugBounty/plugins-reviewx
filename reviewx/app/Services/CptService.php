<?php

namespace Rvx\Services;

use Rvx\Apiz\Http\Response;
use Rvx\Api\CptApi;
use Rvx\Utilities\Auth\Client;
class CptService
{
    /**
     * @return Response
     */
    public function cptGet()
    {
        return (new CptApi())->cptGet();
    }
    public function cptStore($request)
    {
        $uid = ['wp_unique_id' => Client::getUid()];
        $data = \array_merge($request, $uid);
        return (new CptApi())->cptStore($data);
    }
    public function cptUpdate($data)
    {
        $uid = $data['uid'];
        return (new CptApi())->cptUpdate($data, $uid);
    }
    public function cptDelete($data)
    {
        return (new CptApi())->cptDelete($data);
    }
    public function cptStatusChange($data)
    {
        $uid = $data['uid'];
        return (new CptApi())->cptStatusChange($data, $uid);
    }
}

<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
use Exception;
class GoogleReviewApi extends \Rvx\Api\BaseApi
{
    /**
     * @return Response
     * @throws Exception
     */
    public function googleReviewGet() : Response
    {
        return $this->get('reviews/google');
    }
    /**
     * @return Response
     * @throws Exception
     */
    public function googleReviewPlaceApi() : Response
    {
        return $this->get('settings/google-place-api/get');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function googleReviewKey(array $data) : Response
    {
        if ($data['disconnect'] == \true) {
            return $this->withJson($data)->post('settings/google-place-api/save?disconnect=' . $data['disconnect']);
        }
        return $this->withJson($data)->post('settings/google-place-api/save');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function googleReviewSetting(array $data) : Response
    {
        if ($data['is_default'] == \true) {
            return $this->withJson($data)->post('settings/google-widget/save?is_default=' . $data['is_default']);
        }
        return $this->withJson($data)->post('settings/google-widget/save');
    }
}

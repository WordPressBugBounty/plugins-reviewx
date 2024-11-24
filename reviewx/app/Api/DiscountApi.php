<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
class DiscountApi extends \Rvx\Api\BaseApi
{
    /**
     * @return Response
     * @throws \Exception
     */
    public function getDiscount() : Response
    {
        return $this->get('discount');
    }
    /**
     * @return Response
     * @throws \Exception
     */
    public function discountSetting() : Response
    {
        return $this->get('discount/settings');
    }
    /**
     * @return Response
     * @throws \Exception
     */
    public function discountSettingsSave($data) : Response
    {
        return $this->withJson($data)->post('discount/settings');
    }
    public function saveDiscount($data) : Response
    {
        return $this->withJson($data)->post('discount');
    }
    public function discountTemplateGet() : Response
    {
        return $this->get('discount/template');
    }
    public function discountTemplatePost($data) : Response
    {
        return $this->withJson($data)->post('discount/template');
    }
    public function discountMessage($data) : Response
    {
        return $this->withJson($data)->post('discount/message');
    }
}

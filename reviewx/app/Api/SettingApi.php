<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
use Rvx\Utilities\Auth\Client;
class SettingApi extends \Rvx\Api\BaseApi
{
    public function getApiReviewSettings() : Response
    {
        return $this->get('reviews/settings/get');
    }
    public function getApiWidgetSettings() : Response
    {
        return $this->get('settings/widget/get');
    }
    public function userCurrentPlan() : Response
    {
        return $this->get('user/current/plan');
    }
    public function getLocalSettings() : Response
    {
        $uid = Client::getUid();
        return $this->get('storefront/' . $uid . '/widgets/settings');
    }
    public function getApiGeneralSettings() : Response
    {
        return $this->get('settings/general/get');
    }
    public function saveApiGeneralSettings($data) : Response
    {
        $url = 'settings/general/save';
        if (isset($data['is_default'])) {
            return $this->withJson($data)->post($url . '?is_default=' . $data['is_default']);
        }
        return $this->withJson($data)->post($url);
    }
    public function saveApiReviewSettings($data)
    {
        $url = 'reviews/settings/save';
        if (isset($data['is_default'])) {
            return $this->withJson($data)->post($url . '?is_default=' . $data['is_default']);
        }
        return $this->withJson($data)->post($url);
    }
    public function saveApiWidgetSettings($data) : Response
    {
        $url = 'settings/widget/save';
        if (isset($data['is_default'])) {
            return $this->withJson($data)->post($url . '?is_default=' . $data['is_default']);
        }
        return $this->withJson($data)->post($url);
    }
}

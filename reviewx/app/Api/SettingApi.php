<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
use Rvx\Utilities\Auth\Client;
class SettingApi extends \Rvx\Api\BaseApi
{
    public function getApiReviewSettings($data) : Response
    {
        $url = 'reviews/settings/get';
        if (isset($data['cpt_type'])) {
            return $this->get($url . '?cpt_type=' . $data['cpt_type']);
        }
        return $this->get($url);
    }
    public function getApiWidgetSettings() : Response
    {
        return $this->get('settings/widget/get');
    }
    public function userCurrentPlan() : Response
    {
        return $this->get('user/current/plan');
    }
    public function getLocalSettings($post_type = null) : Response
    {
        $uid = Client::getUid();
        if ($post_type === null) {
            $post_type = 'product';
        }
        return $this->get('storefront/' . $uid . '/widgets/settings?cpt_type=' . $post_type);
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

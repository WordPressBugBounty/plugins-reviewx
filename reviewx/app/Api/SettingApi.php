<?php

namespace Rvx\Api;

use Exception;
use Rvx\Apiz\Http\Response;
use Rvx\Utilities\Auth\Client;
class SettingApi extends \Rvx\Api\BaseApi
{
    public function getReviewSettings() : Response
    {
        return $this->get('reviews/settings/get');
    }
    public function getWidgetSettings() : Response
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
    public function getGeneralSettings() : Response
    {
        return $this->get('settings/general/get');
    }
    public function saveGeneralSettings($data) : Response
    {
        $url = 'settings/general/save';
        if (isset($data['is_default'])) {
            return $this->withJson($data)->post($url . '?is_default=' . $data['is_default']);
        }
        return $this->withJson($data)->post($url);
    }
    public function saveReviewSettings($data)
    {
        $url = 'reviews/settings/save';
        if (isset($data['is_default'])) {
            return $this->withJson($data)->post($url . '?is_default=' . $data['is_default']);
        }
        return $this->withJson($data)->post($url);
    }
    public function saveReviewSettingsWoocommer($data)
    {
        $url = 'reviews/settings/save';
        if (isset($data['is_default'])) {
            return $this->withJson($data)->post($url . '?is_default=' . $data['is_default']);
        }
        return $this->withJson($data)->post($url);
    }
    public function saveWidgetSettings($data) : Response
    {
        $url = 'settings/widget/save';
        if (isset($data['is_default'])) {
            return $this->withJson($data)->post($url . '?is_default=' . $data['is_default']);
        }
        return $this->withJson($data)->post($url);
    }
}

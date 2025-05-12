<?php

namespace Rvx\Services;

use Rvx\Apiz\Http\Response;
use Rvx\Api\GoogleReviewApi;
class GoogleReviewService extends \Rvx\Services\Service
{
    /**
     * @return Response
     */
    public function googleReviewGet()
    {
        return (new GoogleReviewApi())->googleReviewGet();
    }
    /**
     * @return Response
     */
    public function googleReviewPlaceApi()
    {
        return (new GoogleReviewApi())->googleReviewPlaceApi();
    }
    public function googleRecaptchaVerify($data)
    {
        $secret = (new \Rvx\Services\SettingService())->getReviewSettings()['reviews']['recaptcha']['secret_key'];
        $token = $data['token'];
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . \urlencode($secret) . '&response=' . \urlencode($token);
        $response = wp_remote_get($recaptcha_url);
        $body = wp_remote_retrieve_body($response);
        $result = \json_decode($body, \true);
        return ['result' => $result['success']];
    }
    public function googleReviewKey($request)
    {
        return (new GoogleReviewApi())->googleReviewKey($request);
    }
    public function googleReviewSetting($request)
    {
        return (new GoogleReviewApi())->googleReviewSetting($request);
    }
}

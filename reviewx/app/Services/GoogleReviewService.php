<?php

namespace Rvx\Services;

use Rvx\Apiz\Http\Response;
use Rvx\Api\GoogleReviewApi;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
class GoogleReviewService extends \Rvx\Services\Service
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
        $secret = Helper::reviewSettings()['reviews']['recaptcha']['secret_key'];
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
    public function googleRecaptcha($data)
    {
        update_option("rvx_recaptch", \json_encode($data));
        return ['message' => __('Recaptch Data Save Successfully', 'reviewx')];
    }
    public function googleRecaptchaData()
    {
        $recaptcha_key = \json_decode(get_option('rvx_recaptch'), \true);
        return ['message' => __('Recaptch Data Save Successfully', 'reviewx'), 'data' => $recaptcha_key];
    }
    public function googleRichSchma($data)
    {
        update_option("rvx_rich_schma", sanitize_text_field($data['schema_enable']));
        return ['message' => __('Review Rich Schema Disable', 'reviewx')];
    }
}

<?php

namespace Rvx\Handlers;

use Rvx\Services\SettingService;
use Rvx\Utilities\Helper;
use Throwable;
use Rvx\WPDrill\Response;
class WoocommerceSettingsSaveHandler
{
    public function wooProductSaveHandler()
    {
        // Isset specific fields
        $isset_label = isset($_POST['woocommerce_review_rating_verification_label']) ? \true : \false;
        $isset_required = isset($_POST['woocommerce_review_rating_verification_required']) ? \true : \false;
        $modifiedReviewsettings = $this->prepareData($isset_label, $isset_required);
        try {
            $response = (new SettingService())->saveApiReviewSettings($modifiedReviewsettings);
            if ($response->getStatusCode() === Response::HTTP_OK) {
                $review_settings = $response->getApiData()['review_settings'];
                $post_type = 'product';
                (new SettingService())->updateReviewSettings($review_settings, $post_type);
            }
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                \error_log('API response - NOT OK: ' . \print_r($response->getApiData()['review_settings'], \true));
                return Helper::rvxApi(['error' => "WC Settings Fail"])->fails('WC Settings Fail', $response->getStatusCode());
            }
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review settings saved failed', $e->getCode());
        }
    }
    private function prepareData($isset_label, $isset_required)
    {
        // Retrieve the existing review settings
        $review_settings = (array) (new SettingService())->getReviewSettings('product')['reviews'];
        $review_settings['show_verified_badge'] = $isset_label ? \true : \false;
        $wcReviewSubmissionPolicy = $isset_required ? \true : \false;
        $isAnyone = \true;
        $isOnlyVerified = \false;
        if ($wcReviewSubmissionPolicy) {
            $isAnyone = \false;
            $isOnlyVerified = \true;
        }
        $review_settings['review_submission_policy']['options']['anyone'] = $isAnyone;
        $review_settings['review_submission_policy']['options']['verified_customer'] = $isOnlyVerified;
        // $review_settings['recaptcha']['secret_key'] = null; // true if verified only [If for some reason data doesn't save, uncomment this and try]
        return $review_settings;
    }
}

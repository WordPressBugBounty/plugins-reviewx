<?php

namespace Rvx\Handlers;

use Rvx\Api\ReviewsApi;
use Rvx\Api\SettingApi;
use Rvx\Api\UserApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Helper;
class WoocommerceSettingsSaveHandler
{
    public function __invoke()
    {
        $data = \json_decode(get_option('rvx_review_settings'), \true);
        $data['reviews']['show_verified_badge'] = isset($_POST['woocommerce_review_rating_verification_label']);
        try {
            $modifiedData = $this->prepareData($data);
            $response = (new SettingApi())->saveReviewSettingsWoocommer($modifiedData);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return Helper::rvxApi(['error' => "WC Settings Fail"])->fails('WC Settings Fail', $response->getStatusCode());
            }
        } catch (\Exception $e) {
            throw new \Rvx\Handlers\Exception(__('An error occurred: ', 'reviewx') . $e->getMessage());
        }
    }
    public function prepareData(array $input) : array
    {
        $wpOnlyVerifiedCustomerSendReview = isset($_POST['woocommerce_review_rating_verification_required']);
        $anyone = \true;
        $onlyVerified = \false;
        if ($wpOnlyVerifiedCustomerSendReview) {
            $anyone = \false;
            $onlyVerified = \true;
        }
        return ["review_submission_policy" => ["options" => ["anyone" => $anyone, "verified_customer" => $onlyVerified]], "show_verified_badge" => (bool) $input['reviews']['show_verified_badge'], "censor_reviewer_name" => (bool) $input['reviews']['censor_reviewer_name'], "review_eligibility" => ["pending_payment" => (bool) $input['reviews']['review_eligibility']['pending_payment'], "processing" => (bool) $input['reviews']['review_eligibility']['processing'], "on_hold" => (bool) $input['reviews']['review_eligibility']['on_hold'], "completed_payment" => (bool) $input['reviews']['review_eligibility']['completed_payment'], "cancelled" => (bool) $input['reviews']['review_eligibility']['cancelled'], "refunded" => (bool) $input['reviews']['review_eligibility']['refunded'], "failed" => (bool) $input['reviews']['review_eligibility']['failed'], "draft" => (bool) $input['reviews']['review_eligibility']['draft']], "auto_approve_reviews" => (bool) $input['reviews']['auto_approve_reviews'], "show_reviewer_name" => (bool) $input['reviews']['show_reviewer_name'], "show_reviewer_country" => (bool) $input['reviews']['show_reviewer_country'], "enable_likes_dislikes" => ["enabled" => (bool) $input['reviews']['enable_likes_dislikes']['enabled'], "options" => ["allow_likes" => (bool) $input['reviews']['enable_likes_dislikes']['options']['allow_likes'], "allow_dislikes" => (bool) $input['reviews']['enable_likes_dislikes']['options']['allow_dislikes']]], "allow_review_sharing" => (bool) $input['reviews']['allow_review_sharing'], "allow_review_titles" => (bool) $input['reviews']['allow_review_titles'], "photo_reviews_allowed" => (bool) $input['reviews']['photo_reviews_allowed'], "video_reviews_allowed" => (bool) $input['reviews']['video_reviews_allowed'], "allow_recommendations" => (bool) $input['reviews']['allow_recommendations'], "anonymous_reviews_allowed" => (bool) $input['reviews']['anonymous_reviews_allowed'], "show_consent_checkbox" => ["enabled" => (bool) isset($input['reviews']['enabled']), "content" => isset($input['reviews']['content'])], "allow_multiple_reviews" => (bool) $input['reviews']['allow_multiple_reviews'], "multicriteria" => ["enable" => (bool) $input['reviews']['multicriteria']['enable'], "criterias" => $input['reviews']['multicriteria']['criterias']], "product_schema" => (bool) $input['reviews']['product_schema'], "recaptcha" => ["enabled" => isset($input['reviews']['recaptcha']['enabled']) ? (bool) $input['reviews']['recaptcha']['enabled'] : \false, "site_key" => $input['reviews']['recaptcha']['site_key'], "secret_key" => $input['reviews']['recaptcha']['secret_key']]];
    }
}

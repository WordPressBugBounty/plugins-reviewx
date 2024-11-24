<?php

namespace Rvx\Rest\Controllers;

use Rvx\WPDrill\Response;
use Rvx\Utilities\Auth\Client;
use Rvx\Services\SettingService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\Utilities\Helper;
class SettingController implements InvokableContract
{
    protected SettingService $settingService;
    /**
     *
     */
    public function __construct()
    {
        $this->settingService = new SettingService();
    }
    /**
     * @return void
     */
    public function __invoke()
    {
    }
    public function getReviewSettings()
    {
        $response = $this->settingService->getReviewSettings();
        return Helper::getApiResponse($response);
    }
    public function wooCommerceVerificationRating()
    {
        $response = $this->settingService->wooCommerceVerificationRating();
        return $response;
    }
    public function wooVerificationRatingRequired()
    {
        $response = $this->settingService->wooVerificationRatingRequired();
        return $response;
    }
    public function userSettingsAccess($request)
    {
        $data = $request->get_params()['user_access'];
        update_option('__user_setting_access', \json_encode($data));
    }
    public function saveReviewSettings($request)
    {
        try {
            $response = $this->settingService->saveReviewSettings($request->get_params());
            if ($response->getStatusCode() == Response::HTTP_OK) {
                if ($response->getApiData()['review_settings']['reviews']['show_verified_badge'] === \true) {
                    update_option('woocommerce_review_rating_verification_label', 'yes');
                }
                if ($response->getApiData()['review_settings']['reviews']['show_verified_badge'] === \false) {
                    update_option('woocommerce_review_rating_verification_label', 'no');
                }
                if ($response->getApiData()['review_settings']['reviews']['review_submission_policy']['options']['verified_customer'] === \true) {
                    update_option('woocommerce_review_rating_verification_required', 'yes');
                }
                if ($response->getApiData()['review_settings']['reviews']['review_submission_policy']['options']['verified_customer'] === \false) {
                    update_option('woocommerce_review_rating_verification_required', 'no');
                }
                update_option('rvx_review_settings', \json_encode($response->getApiData()['review_settings']));
            }
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review settings saved failed', $e->getCode());
        }
    }
    public function wooCommerceVerificationRatingUpdate($request)
    {
        try {
            $response = $this->settingService->wooCommerceVerificationRatingUpdate($request->get_params());
            return $response;
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review settings saved failed', $e->getCode());
        }
    }
    public function wooVerificationRating($request)
    {
        try {
            $response = $this->settingService->wooVerificationRating($request->get_params());
            return $response;
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review settings saved failed', $e->getCode());
        }
    }
    public function getWidgetSettings()
    {
        $response = $this->settingService->getWidgetSettings();
        return Helper::getApiResponse($response);
    }
    public function userCurrentPlan()
    {
        $response = $this->settingService->userCurrentPlan();
        return Helper::getApiResponse($response);
    }
    public function saveWidgetSettings($request)
    {
        try {
            $response = $this->settingService->saveWidgetSettings($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Widget settings saved failed', $e->getCode());
        }
    }
    public function getGeneralSettings()
    {
        $response = $this->settingService->getGeneralSettings();
        return Helper::getApiResponse($response);
    }
    public function saveGeneralSettings($request)
    {
        try {
            $response = $this->settingService->saveGeneralSettings($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('General settings saved failed', $e->getCode());
        }
    }
    public function getSettingsData()
    {
        $data = ["review_settings" => ["reviews" => ["review_submission_policy" => ["options" => ["anyone" => \true, "verified_customer" => \false]], "show_verified_badge" => \false, "review_eligibility" => ["pending_payment" => \false, "processing" => \false, "on_hold" => \false, "completed_payment" => \true, "cancelled" => \false, "refunded" => \false, "failed" => \false, "draft" => \false], "auto_approve_reviews" => \true, "show_reviewer_name" => \true, "show_reviewer_country" => \true, "enable_likes_dislikes" => ["enabled" => \true, "options" => ["allow_likes" => \true, "allow_dislikes" => \false]], "allow_review_sharing" => \true, "allow_review_titles" => \true, "photo_reviews_allowed" => \true, "video_reviews_allowed" => \true, "allow_recommendations" => \true, "anonymous_reviews_allowed" => \true, "show_consent_checkbox" => \true, "allow_multiple_reviews" => \true, "multi_criteria_reviews" => ["enabled" => \true, "criteria" => ["Quality", "Price", "Size"]]]]];
        $json_data = \json_encode($data);
        update_option('your_option_name', $json_data);
    }
    public function dataSyncStatus()
    {
        return ['sync' => Client::getSync() ? \true : \false];
    }
    public function allSettingsSave($request)
    {
        try {
            $response = $this->settingService->allSettingsSave($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('General settings saved failed', $e->getCode());
        }
    }
    public function removeCredentials()
    {
        try {
            $response = $this->settingService->removeCredentials();
            return $response;
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('General settings saved failed', $e->getCode());
        }
    }
}

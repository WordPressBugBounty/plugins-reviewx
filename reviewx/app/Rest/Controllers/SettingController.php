<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\SettingService;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Throwable;
use Rvx\WPDrill\Response;
class SettingController
{
    protected SettingService $settingService;
    public function __construct()
    {
        $this->settingService = new SettingService();
    }
    public function getApiReviewSettingsOnSync($cpt_type = 'product') : array
    {
        $data = ["cpt_type" => $cpt_type];
        $response = $this->settingService->getApiReviewSettings($data);
        return \json_decode($response, \true);
    }
    public function getApiReviewSettings($request) : Response
    {
        $cpt_type = \strtolower($request->get_param('cpt_type')) ? \strtolower($request->get_param('cpt_type')) : \false;
        $data = ["cpt_type" => $cpt_type];
        $response = $this->settingService->getApiReviewSettings($data);
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
    public function saveApiReviewSettings($request)
    {
        try {
            $response = $this->settingService->saveApiReviewSettings($request->get_params());
            if ($response->getStatusCode() === Response::HTTP_OK) {
                // Update Review Settings
                $review_settings = $response->getApiData()['review_settings'];
                if (!empty($review_settings['reviews']['show_verified_badge']) && $review_settings['reviews']['show_verified_badge'] === \true) {
                    update_option('woocommerce_review_rating_verification_label', 'yes');
                }
                if (!empty($review_settings['reviews']['show_verified_badge']) && $review_settings['reviews']['show_verified_badge'] === \false) {
                    update_option('woocommerce_review_rating_verification_label', 'no');
                }
                if (!empty($review_settings['reviews']['review_submission_policy']['options']['verified_customer']) && $review_settings['reviews']['review_submission_policy']['options']['verified_customer'] === \true) {
                    update_option('woocommerce_review_rating_verification_required', 'yes');
                }
                if (!empty($review_settings['reviews']['review_submission_policy']['options']['verified_customer']) && $review_settings['reviews']['review_submission_policy']['options']['verified_customer'] === \false) {
                    update_option('woocommerce_review_rating_verification_required', 'no');
                }
                $this->settingService->updateReviewSettings($review_settings, $request['post_type']);
            }
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review settings saved failed', $e->getCode());
        }
    }
    public function wooCommerceVerificationRatingUpdate($request)
    {
        try {
            $response = $this->settingService->wooCommerceVerificationRatingUpdate($request->get_params());
            return $response;
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review settings saved failed', $e->getCode());
        }
    }
    public function wooVerificationRating($request)
    {
        try {
            $response = $this->settingService->wooVerificationRating($request->get_params());
            return $response;
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review settings saved failed', $e->getCode());
        }
    }
    public function getApiWidgetSettingsOnSync() : array
    {
        $response = $this->settingService->getApiWidgetSettings();
        return \json_decode($response, \true);
    }
    public function getApiWidgetSettings() : Response
    {
        $response = $this->settingService->getApiWidgetSettings();
        return Helper::getApiResponse($response);
    }
    public function userCurrentPlan()
    {
        $response = $this->settingService->userCurrentPlan();
        return Helper::getApiResponse($response);
    }
    public function saveApiWidgetSettings($request)
    {
        try {
            $response = $this->settingService->saveWidgetSettings($request->get_params());
            if ($response->getStatusCode() === Response::HTTP_OK) {
                // Update Widget Settings
                $widget_settings = $response->getApiData()['widget_settings'];
                $this->settingService->updateWidgetSettings($widget_settings);
            }
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Widget settings saved failed', $e->getCode());
        }
    }
    public function getApiGeneralSettings()
    {
        $response = $this->settingService->getApiGeneralSettings();
        return Helper::getApiResponse($response);
    }
    public function saveApiGeneralSettings($request)
    {
        try {
            $response = $this->settingService->saveApiGeneralSettings($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('General settings saved failed', $e->getCode());
        }
    }
    public function removeCredentials()
    {
        try {
            $response = $this->settingService->removeCredentials();
            return $response;
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('General settings saved failed', $e->getCode());
        }
    }
}

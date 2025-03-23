<?php

namespace Rvx\Rest\Controllers;

use Exception;
use Throwable;
use Rvx\WPDrill\Response;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\Services\Api\LoginService;
use Rvx\Services\ReviewService;
use Rvx\Services\SettingService;
use Rvx\Utilities\Helper;
class StoreFrontReviewController implements InvokableContract
{
    protected ReviewService $reviewService;
    protected SettingService $settingService;
    protected LoginService $loginService;
    public function __construct()
    {
        $this->reviewService = new ReviewService();
        $this->settingService = new SettingService();
        $this->loginService = new LoginService();
    }
    /**
     * @return void
     */
    public function __invoke()
    {
    }
    public function settingMeta($request) : Response
    {
        try {
            $this->reviewService->settingMeta($request);
            return Helper::rest()->success("Success");
        } catch (Exception $e) {
            return Helper::rest()->fails("Fails");
        }
    }
    public function dataGetFromSaas($request)
    {
        $response = $this->reviewService->getWidgetReviewsForProduct($request);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return \false;
        }
        return $response->getApiData();
    }
    public function getWidgetReviewsForProduct($request)
    {
        $diffReviewCount = $this->reviewCountDifferent($request["product_id"]);
        if ($diffReviewCount === \true) {
            delete_post_meta($request["product_id"], '_rvx_latest_reviews');
            return $this->dataGetFormSaas($request);
        }
        $postMata = get_post_meta($request["product_id"], "_rvx_latest_reviews", \true);
        if ($postMata) {
            if (\count($postMata["reviews"]) != $this->insightReviewCount($request["product_id"])) {
                delete_post_meta($request["product_id"], '_rvx_latest_reviews');
                return $this->dataGetFormSaas($request);
            }
            if ($request->get_param("cursor") || $request->get_param("rating") || $request->get_param("sortBy") || $request->get_param("attachment")) {
                $response = $this->reviewService->getWidgetReviewsForProduct($request);
                return Helper::saasResponse($response);
            } else {
                if ($this->is_valid_data($postMata)) {
                    //valid
                    $response = ["reviews" => $postMata["reviews"], "meta" => $postMata["meta"]];
                    if ($response) {
                        return Helper::rest($response)->success("Success");
                    } else {
                        return Helper::rest()->fails("Fails");
                    }
                } else {
                    //invalid
                    $this->loginService->resetProductWisePostMeta($request["product_id"]);
                    return $this->dataGetFormSaas($request);
                }
            }
        } else {
            return $this->dataGetFormSaas($request);
        }
    }
    public function dataGetFormSaas($request)
    {
        $latestReview = $this->dataGetFromSaas($request);
        if (!$latestReview) {
            return Helper::rvxApi(["error" => "Fails"])->fails("failed");
        }
        $reviews = Helper::arrayGet($latestReview, "reviews");
        if (\count($reviews) > 0) {
            $this->reviewService->postMetaReviewInsert($request->get_param("product_id"), $latestReview);
        }
        return ["data" => $latestReview];
    }
    public function is_valid_data($postMata)
    {
        if (\is_array($postMata)) {
            return \true;
        }
        return \false;
    }
    public function insightDataGetInSaas($request)
    {
        $response = $this->reviewService->getWidgetInsight($request);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return \false;
        }
        return $response->getApiData();
    }
    public function getWidgetInsight($request)
    {
        try {
            $diffReviewCount = $this->reviewCountDifferent($request["product_id"]);
            if ($diffReviewCount === \true) {
                return ["data" => $this->insightDataelsePart($request)];
            }
            $data = get_post_meta($request["product_id"], "_rvx_latest_reviews_insight", \true);
            if ($data) {
                $aggregation = \json_decode($data, \true);
                if (\is_array($aggregation)) {
                    $aggregation['product']['title'] = \htmlspecialchars_decode($aggregation['product']['title'], \ENT_QUOTES);
                }
                if (!\is_array($aggregation)) {
                    $modData = $this->productTitleAndDescriptionBackSlashRemove($data);
                    $aggregation = \json_decode($modData, \true);
                }
                if ($aggregation) {
                    return Helper::rest($aggregation)->success("Success");
                } else {
                    return Helper::rest()->fails("Fails");
                }
            } else {
                // Fetch the latest aggregation data
                return ["data" => $this->insightDataelsePart($request)];
            }
        } catch (Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("failed", $e->getCode());
        }
    }
    public function insightDataelsePart($request)
    {
        $latestAggregation = $this->insightDataGetInSaas($request);
        // Check if the data retrieval fails
        if (!$latestAggregation) {
            return Helper::rvxApi(["error" => "Fails"])->fails("failed");
        }
        // Extract 'criteria_stats' from the latest aggregation data (always an array)
        $criteriaStat = Helper::arrayGet($latestAggregation, "criteria_stats");
        // No need to decode since it's always an array, just assign back
        $latestAggregation["criteria_stats"] = $criteriaStat;
        delete_post_meta($request->get_param("product_id"), '_rvx_latest_reviews_insight');
        // Store the data in post meta as a JSON string
        update_post_meta($request->get_param("product_id"), "_rvx_latest_reviews_insight", \json_encode($latestAggregation, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES));
        return $latestAggregation;
    }
    public function productTitleAndDescriptionBackSlashRemove($data)
    {
        return \preg_replace('/("title":")([^"\\\\]+)\\\\\'/', '$1$2\'', $data);
    }
    public function reviewCountDifferent($id) : bool
    {
        $wpReview = $this->getOnlyApproveReviewCount($id);
        $saasReview = $this->insightReviewCount($id);
        if ($wpReview > $saasReview) {
            return \true;
        }
        if ($wpReview < $saasReview) {
            return \true;
        }
        return \false;
    }
    public function getOnlyApproveReviewCount($id) : int
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT COUNT(*) \n             FROM {$wpdb->comments} \n             WHERE comment_post_ID = %d \n             AND comment_approved = '1' \n             AND comment_parent = 0\n             AND comment_type IN ('comment', 'review')", $id);
        return (int) $wpdb->get_var($query);
    }
    public function insightReviewCount($id) : int
    {
        if (metadata_exists('post', $id, '_rvx_latest_reviews_insight')) {
            $data = get_post_meta($id, "_rvx_latest_reviews_insight", \true);
            $reviewAggregation = \json_decode($data, \true);
            return $reviewAggregation['aggregation']['total_reviews'] ?? 0;
        }
        return 0;
    }
    /**
     * @param $request
     * @return Response
     */
    public function saveWidgetReviewsForProduct($request)
    {
        try {
            $response = $this->reviewService->saveWidgetReviewsForProduct($request);
            $this->reviewService->removeCache();
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("failed", $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function requestReviewEmailAttachment($request) : Response
    {
        try {
            $data = $this->reviewService->requestReviewEmailAttachment($request);
            return Helper::rvxApi(["reviews" => $data])->success("Review Successfully sent", 200);
        } catch (Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("failed", $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function likeDIslikePreference($request)
    {
        try {
            $response = $this->reviewService->likeDIslikePreference($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("failed", $e->getCode());
        }
    }
    public function singleActionProductMata($request)
    {
        $reviewData = Helper::arrayGet($request->get_params(), "review");
        $aggregationData = Helper::arrayGet($request->get_params(), "aggregation");
        $productId = $aggregationData["product_wp_id"];
        $this->storeReviewMeta($productId, $reviewData);
        $this->storeAggregationMeta($productId, $aggregationData);
    }
    public function storeReviewMeta($productId, $payload)
    {
        $reviewAndMeta = ["reviews" => Helper::arrayGet($payload, "reviews"), "meta" => Helper::arrayGet($payload, "meta")];
        $this->reviewService->postMetaReviewInsert($productId, $reviewAndMeta);
    }
    public function storeAggregationMeta($productId, $payload)
    {
        $aggregation_data = \json_encode(wp_slash(Helper::arrayGet($payload, "meta")), \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
        update_post_meta($productId, "_rvx_latest_reviews_insight", $aggregation_data);
    }
    public function reviewRequestStoreItem($request)
    {
        try {
            $review = $this->reviewService->reviewRequestStoreItem($request->get_params());
            return $review;
            //            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("failed", $e->getCode());
        }
    }
    public function thanksMessage($request)
    {
        try {
            $response = $this->reviewService->thanksMessage($request);
            return $response;
        } catch (Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("failed", $e->getCode());
        }
    }
    public function getSpecificReviewItem($request)
    {
        try {
            $resp = get_option('_review_shortcode');
            if ($resp) {
                $data = ['reviews' => $resp['reviews'], 'meta' => $resp['meta']];
                return Helper::rest($data)->success("Success");
            } else {
                $response = $this->reviewService->getSpecificReviewItem($request);
                update_option('_review_shortcode', $response->getApiData());
                return Helper::saasResponse($response);
            }
        } catch (Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("Specific Review Item Fails", $e->getCode());
        }
    }
    public function getLocalSettings($request)
    {
        // Get API param
        $post_type = $request->get_param('cpt_type') ? \strtolower($request->get_param('cpt_type')) : 'product';
        $data = (array) (new SettingService())->getSettingsData($post_type) ?? [];
        if ($data) {
            return Helper::rest($data)->success("Success");
        } else {
            $response = $this->settingService->getLocalSettings($post_type);
            $apiResponse = Helper::getApiResponse($response);
            $review_setting = $apiResponse->data['data']['setting']['review_settings'];
            $this->settingService->updateReviewSettings($review_setting, $post_type);
            return $apiResponse;
        }
    }
}

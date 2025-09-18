<?php

namespace Rvx\Rest\Controllers;

use Exception;
use Rvx\Services\Api\LoginService;
use Rvx\Services\ReviewService;
use Rvx\Services\CacheServices;
use Rvx\Services\SettingService;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Throwable;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
class StoreFrontReviewController implements InvokableContract
{
    protected ReviewService $reviewService;
    protected SettingService $settingService;
    protected LoginService $loginService;
    protected CacheServices $cacheService;
    public function __construct()
    {
        $this->reviewService = new ReviewService();
        $this->settingService = new SettingService();
        $this->loginService = new LoginService();
        $this->cacheService = new CacheServices();
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
            \delete_transient("rvx_{$request["product_id"]}_latest_reviews");
            return $this->dataGetFormSaas($request);
        }
        $productId = $request["product_id"];
        $postMata = \get_transient("rvx_{$productId}_latest_reviews");
        if ($postMata) {
            if (\count($postMata["reviews"]) != $this->insightReviewCount($request["product_id"])) {
                \delete_transient("rvx_{$request["product_id"]}_latest_reviews");
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
    public function getAllReviewsDataFromSaas($request, $site_id)
    {
        $response = $this->reviewService->getWidgetAllReviewsForSite($request, $site_id);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return \false;
        }
        return $response->getApiData();
    }
    public function getWidgetAllReviewsListForSite($request)
    {
        $site_id = Client::getUid();
        $post_type = $request['post_type'] ?? 'all';
        $postMata = \get_transient("rvx_{$site_id}_{$post_type}_reviews");
        if ($post_type === 'all') {
            $post_type = null;
        }
        if (!$postMata) {
            return $this->getAllReviewsFromSaas($request, $site_id, $post_type);
        }
        if ($request->get_param("cursor") || $request->get_param("rating") || $request->get_param("sort_by") || $request->get_param("attachment")) {
            $response = $this->reviewService->getWidgetAllReviewsForSite($request, $site_id);
            return Helper::saasResponse($response);
        }
        if ($this->is_valid_data($postMata)) {
            $response = ["reviews" => $postMata["reviews"], "meta" => $postMata["meta"]];
            if ($response) {
                return Helper::rest($response)->success("Success");
            }
            return Helper::rest()->fails("Fails");
        }
        // invalid cached data → reset and fetch fresh
        $this->resetSiteWiseTransientMeta($site_id, $post_type);
        return $this->getAllReviewsFromSaas($request, $site_id, $post_type);
    }
    public function resetSiteWiseTransientMeta($site_id, $post_type)
    {
        global $wpdb;
        $post_type = $post_type != null ? $post_type : 'all';
        $review_key = "rvx_{$site_id}_{$post_type}_reviews";
        // Prepare and execute the deletion query
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s", $review_key));
    }
    public function getWidgetReviewsListShortcode($request)
    {
        $productId = $request['product_id'];
        // mismatch in review count → reset + fetch
        if ($this->reviewCountDifferent($productId)) {
            \delete_transient("rvx_{$productId}_latest_reviews");
            return $this->dataGetFormSaas($request);
        }
        $postMeta = \get_transient("rvx_{$productId}_latest_reviews");
        // no cache → fetch
        if (!$postMeta) {
            return $this->dataGetFormSaas($request);
        }
        // cache count mismatch → reset + fetch
        if (\count($postMeta['reviews']) !== $this->insightReviewCount($productId)) {
            \delete_transient("rvx_{$productId}_latest_reviews");
            return $this->dataGetFormSaas($request);
        }
        // request has filters → always fetch fresh
        if ($request->get_param('cursor') || $request->get_param('rating') || $request->get_param('sortBy') || $request->get_param('attachment')) {
            $response = $this->reviewService->getWidgetReviewsListShortcode($request);
            return Helper::saasResponse($response);
        }
        // cached data valid → return
        if ($this->is_valid_data($postMeta)) {
            return Helper::rest(['reviews' => $postMeta['reviews'], 'meta' => $postMeta['meta']])->success('Success');
        }
        // invalid cached data → reset + fetch
        $this->loginService->resetProductWisePostMeta($productId);
        return $this->dataGetFormSaas($request);
    }
    public function getAllReviewsFromSaas($request, $site_id, $post_type)
    {
        $latestReviews = $this->getAllReviewsDataFromSaas($request, $site_id);
        if (!$latestReviews) {
            return Helper::rvxApi(["error" => "Fails"])->fails("failed");
        }
        $reviews = Helper::arrayGet($latestReviews, "reviews");
        if (\count($reviews) > 0) {
            $this->reviewService->setAllReviewsMetaTransient($site_id, $post_type, $latestReviews);
        }
        return ["data" => $latestReviews];
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
            $data = \get_transient("rvx_{$request["product_id"]}_latest_reviews_insight");
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
        \delete_transient("rvx_{$request->get_param("product_id")}_latest_reviews_insight");
        // Store the data in post meta as a JSON string
        $latestAggregationJson = \json_encode($latestAggregation, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
        set_transient("rvx_{$request->get_param("product_id")}_latest_reviews_insight", $latestAggregationJson, 604800);
        // Expires in 7 days
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
            $data = \get_transient("rvx_{$id}_latest_reviews_insight");
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
            $this->cacheService->removeCache();
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
    public function likeDislikePreference($request)
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
        set_transient("rvx_{$productId}_latest_reviews_insight", $aggregation_data, 604800);
        // Expires in 7 days
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
            $defferentIds = $this->cacheService->clearShortcodesCache(get_option('_rvx_reviews_ids'), $request->get_params());
            if ($defferentIds == \false) {
                \delete_transient('_rvx_shortcode_transient');
            }
            $transient_key = '_rvx_shortcode_transient';
            $resp = \get_transient($transient_key);
            if ($resp !== \false) {
                $data = ['reviews' => $resp['reviews'], 'meta' => $resp['meta']];
                return Helper::rest($data)->success("Success (from transient)");
            } else {
                $response = $this->reviewService->getSpecificReviewItem($request);
                $api_data = $response->getApiData();
                set_transient($transient_key, $api_data, 12 * HOUR_IN_SECONDS);
                update_option('_rvx_reviews_ids', $request->get_params());
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

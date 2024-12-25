<?php

namespace Rvx\Rest\Controllers;

use Rvx\PHPUnit\Util\Exception;
use Rvx\Services\Api\LoginService;
use Rvx\WPDrill\Response;
use Rvx\Services\ReviewService;
use Rvx\Services\SettingService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\Utilities\Helper;
class StoreFrontReviewController implements InvokableContract
{
    protected ReviewService $reviewService;
    protected ReviewService $wpReviewService;
    protected SettingService $settingService;
    protected LoginService $loginService;
    /**
     *
     */
    public function __construct()
    {
        $this->reviewService = new ReviewService();
        $this->wpReviewService = new ReviewService();
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
        $postMata = get_post_meta($request["product_id"], "_rvx_latest_reviews", \true);
        if ($postMata) {
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
                $latestAggregation = $this->insightDataGetInSaas($request);
                $latestAggregation['product']['description'] = \strip_tags($latestAggregation['product']['description']);
                // Check if the data retrieval fails
                if (!$latestAggregation) {
                    return Helper::rvxApi(["error" => "Fails"])->fails("failed");
                }
                // Extract 'criteria_stats' from the latest aggregation data (always an array)
                $criteriaStat = Helper::arrayGet($latestAggregation, "criteria_stats");
                // No need to decode since it's always an array, just assign back
                $latestAggregation["criteria_stats"] = $criteriaStat;
                // Store the data in post meta as a JSON string
                update_post_meta($request->get_param("product_id"), "_rvx_latest_reviews_insight", \json_encode($latestAggregation, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES));
                return ["data" => $latestAggregation];
            }
        } catch (\Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("failed", $e->getCode());
        }
    }
    public function productTitleAndDescriptionBackSlashRemove($data)
    {
        $dataModify = \preg_replace('/("title":")([^"\\\\]+)\\\\\'/', '$1$2\'', $data);
        return \preg_replace('/("description":\\s*")([^"\\\\]|\\\\.)*"/', '$1Description"', $dataModify);
    }
    /**
     * @param $request
     * @return Response
     */
    public function saveWidgetReviewsForProduct($request)
    {
        try {
            $response = $this->reviewService->saveWidgetReviewsForProduct($request);
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("failed", $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function requestReviewEmailAttachment($request)
    {
        try {
            $response = $this->reviewService->requestReviewEmailAttachment($request);
            return $response;
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("failed", $e->getCode());
        }
    }
    public function thanksMessage($request)
    {
        try {
            $response = $this->reviewService->thanksMessage($request);
            return $response;
        } catch (\Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("failed", $e->getCode());
        }
    }
    public function getSpecificReviewItem($request)
    {
        try {
            $response = $this->reviewService->getSpecificReviewItem($request);
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("Specific Review Item Fails", $e->getCode());
        }
    }
    public function getLocalSettings()
    {
        $data = get_option("_rvx_settings_data");
        if ($data) {
            return Helper::rest($data)->success("Success");
        } else {
            $resp = $this->settingService->getLocalSettings();
            return Helper::getApiResponse($resp);
        }
    }
}

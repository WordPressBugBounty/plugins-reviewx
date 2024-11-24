<?php

namespace Rvx\Rest\Controllers;

use Rvx\PHPUnit\Util\Exception;
use Rvx\WPDrill\Response;
use Rvx\WPDrill\Facades\Request;
use Rvx\Utilities\Auth\Client;
use Rvx\Services\ReviewService;
use Rvx\Services\SettingService;
use Rvx\Services\DataSyncService;
use Rvx\Handlers\StoreFrontHandller;
use Rvx\Services\Wp\WpReviewService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\Utilities\Helper;
class StoreFrontReviewController implements InvokableContract
{
    protected ReviewService $reviewService;
    protected ReviewService $wpReviewService;
    protected SettingService $settingService;
    /**
     *
     */
    public function __construct()
    {
        $this->reviewService = new ReviewService();
        $this->wpReviewService = new ReviewService();
        $this->settingService = new SettingService();
    }
    /**
     * @return void
     */
    public function __invoke()
    {
    }
    public function dataCheckInLocal($data)
    {
        if ($data) {
            \error_log("true check >");
            return \true;
        }
        \error_log("false check >");
        return \false;
    }
    public function datagetInWp($data)
    {
        return $data;
    }
    public function datagetInSaas($request)
    {
        $response = $this->reviewService->getWidgetReviewsForProduct($request);
        $latestReview = $response->getApiData();
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return Helper::rvxApi(["error" => "Fails"])->fails("failed");
        }
        update_post_meta($request->get_param("product_id"), "_rvx_latest_reviews", \json_encode($latestReview));
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
                $reviews = \json_decode($postMata, \true);
                $response = ["reviews" => $reviews["reviews"], "meta" => $reviews["meta"]];
                if ($response) {
                    return Helper::rest($response)->success("Success");
                } else {
                    return Helper::rest()->fails("Fails");
                }
                // wp_send_json($response);
            }
        } else {
            $latestReview = $this->dataGetFromSaas($request);
            if (!$latestReview) {
                return Helper::rvxApi(["error" => "Fails"])->fails("failed");
            }
            $reviews = Helper::arrayGet($latestReview, "reviews");
            if (\count($reviews) > 0) {
                update_post_meta($request->get_param("product_id"), "_rvx_latest_reviews", \json_encode($latestReview));
            }
            return ["data" => $latestReview];
        }
    }
    public function prepareReviewExtraData($comment)
    {
        $meta_data = get_comment_meta($comment->comment_ID);
        $replay_content = $this->reviewReplay($comment->comment_ID);
        return [
            "wp_unique_id" => Client::getUid() . "-" . $comment->comment_ID,
            "title" => get_comment_meta($comment->comment_ID, "reviewx_title", \true),
            "feedback" => $comment->comment_content,
            "reviewer_name" => $comment->comment_author,
            "reviewer_email" => $comment->comment_author_email,
            "rating" => get_comment_meta($comment->comment_ID, "rating", \true),
            "created_at" => $comment->comment_date,
            "criterias" => (new DataSyncService())->multiCriteria($meta_data),
            "attachments" => $this->attachments($comment->comment_ID) ?? [],
            "is_verified" => get_comment_meta($comment->comment_ID, "verified", \true),
            // 'is_customer_verified' => get_comment_meta($comment->comment_ID, 'reviewx_anonymouse_user', true),
            "is_anonymous" => get_comment_meta($comment->comment_ID, "reviewx_anonymouse_user", \true),
            // 'is_highlighted' => get_comment_meta($comment->comment_ID, 'reviewx_anonymouse_user', true),
            // 'product_title' => get_comment_meta($comment->comment_ID, 'reviewx_anonymouse_user', true),
            "product_wp_id" => $comment->comment_post_ID,
            "status" => (new DataSyncService())->getCommentStatus($comment),
            "likes" => get_comment_meta($comment->comment_ID, "comment_like") ? get_comment_meta($comment->comment_ID, "comment_like") : 0,
            "dislikes" => 0,
            "reply" => $replay_content["reply_content"] ?? "",
            "replied_at" => $replay_content["reply_date"] ?? "",
            "preference" => 0,
            "customer" => $this->reviewUserData($comment->user_id),
        ];
    }
    public function reviewUserData($user_id)
    {
        return ["wp_id" => $user_id, "name" => get_user_meta($user_id, "first_name", \true) . " " . get_user_meta($user_id, "first_name", \true), "email" => get_user_meta($user_id, "user_email", \true), "avatar" => get_avatar_url($user_id), "city" => get_user_meta($user_id, "billing_city", \true) ?? "", "phone" => get_user_meta($user_id, "billing_phone", \true) ?? "", "address" => get_user_meta($user_id, "billing_address_1", \true) ?? "", "country" => get_user_meta($user_id, "billing_country", \true) ?? "", "status" => 1];
    }
    private function attachments($comment_id)
    {
        $attachments = get_comment_meta($comment_id, "reviewx_attachments", \true) ?? \false;
        $video_url = get_comment_meta($comment_id, "reviewx_video_url", \true) ?? \false;
        $data = \unserialize($attachments);
        if ($data !== \false && isset($data["images"])) {
            $links = [];
            foreach ($data["images"] as $i => $image_id) {
                $links[] = wp_get_attachment_url($image_id);
            }
            if ($video_url) {
                $links[] = $video_url;
            }
            return $links;
        }
    }
    public function reviewReplay($comment_id)
    {
        $reply_args = ["parent" => $comment_id, "status" => "approve", "orderby" => "comment_date", "order" => "ASC"];
        $replies = get_comments($reply_args);
        $replies_data = [];
        foreach ($replies as $reply) {
            $replies_data[] = ["id" => $reply->comment_ID, "reply_author" => $reply->comment_author, "reply_content" => $reply->comment_content, "reply_date" => $reply->comment_date];
        }
        return $replies_data;
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
                if ($aggregation) {
                    return Helper::rest($aggregation)->success("Success");
                } else {
                    return Helper::rest()->fails("Fails");
                }
            } else {
                // Fetch the latest aggregation data
                $latestAggregation = $this->insightDataGetInSaas($request);
                // Check if the data retrieval fails
                if (!$latestAggregation) {
                    return Helper::rvxApi(["error" => "Fails"])->fails("failed");
                }
                // Extract 'criteria_stats' from the latest aggregation data (always an array)
                $criteriaStat = Helper::arrayGet($latestAggregation, "criteria_stats");
                // No need to decode since it's always an array, just assign back
                $latestAggregation["criteria_stats"] = $criteriaStat;
                // Store the data in post meta as a JSON string
                update_post_meta($request->get_param("product_id"), "_rvx_latest_reviews_insight", \json_encode($latestAggregation));
                return ["data" => $latestAggregation];
            }
        } catch (\Throwable $e) {
            return Helper::rvxApi(["error" => $e->getMessage()])->fails("failed", $e->getCode());
        }
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
        $latest_ten_review = \json_encode($reviewAndMeta, \true);
        update_post_meta($productId, "_rvx_latest_reviews", $latest_ten_review);
    }
    public function storeAggregationMeta($productId, $payload)
    {
        $aggregation_data = \json_encode(Helper::arrayGet($payload, "meta"), \true);
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

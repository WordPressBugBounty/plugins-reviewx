<?php

namespace Rvx\Rest\Controllers;

use Exception;
use Throwable;
use Rvx\Services\ReviewService;
use Rvx\Utilities\Helper;
use Rvx\Services\CacheServices;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
class ReviewController implements InvokableContract
{
    protected ReviewService $reviewService;
    protected CacheServices $cacheServices;
    public function __construct()
    {
        $this->reviewService = new ReviewService();
        $this->cacheServices = new CacheServices();
    }
    /**
     * @return void
     */
    public function __invoke()
    {
    }
    /**
     * @return Response
     */
    public function index($request)
    {
        $aggregation = \get_transient('rvx_admin_aggregation');
        if (empty($request->get_params()) && $aggregation) {
            $response = ['aggregations' => $aggregation['aggregations'], 'count' => $aggregation['count'], 'reviews' => $aggregation['reviews'], 'meta' => $aggregation['meta']];
            return Helper::rest($response)->success("Success");
        } else {
            $resp = $this->reviewService->getReviews($request->get_params());
            if ($resp->getStatusCode() === Response::HTTP_OK) {
                $this->aggregationDataStore($resp->getApiData());
            }
            return Helper::getApiResponse($resp);
        }
    }
    public function aggregationDataStore($data)
    {
        \delete_transient('rvx_admin_aggregation');
        set_transient('rvx_admin_aggregation', $data, 3600);
    }
    public function adminAllReviewSaasCall($data)
    {
        $resp = $this->reviewService->reviewList($data);
        $isVisible = $data['isVisible'] ?? '';
        if ($resp->getStatusCode() === Response::HTTP_OK) {
            $this->storeVisibilityReview($resp->getApiData(), $isVisible);
        }
        return $this->enrichReviewList($resp);
    }
    private function enrichReviewList($resp)
    {
        if ($resp->getStatusCode() === Response::HTTP_OK) {
            $data = $resp->getApiData();
            if (isset($data['reviews']) && \is_array($data['reviews'])) {
                foreach ($data['reviews'] as &$review) {
                    if (isset($review['wp_post_id'])) {
                        $review['post_type'] = get_post_type($review['wp_post_id']);
                    }
                }
                return Helper::rest($data)->success($resp()->message, $resp->getStatusCode());
            }
        }
        return Helper::getApiResponse($resp);
    }
    public function reviewList($request)
    {
        try {
            $differentReview = $this->cacheServices->makeSaaSCallDecision();
            $this->visibilityPaginationSaasCall($request, $differentReview);
            $isVisible = $request->get_params()['isVisible'] ?? '';
            $transientKeys = ['published' => 'rvx_review_approve_data', 'pending' => 'rvx_review_pending_data', 'spam' => 'rvx_review_spam_data', 'trash' => 'rvx_review_trash_data'];
            if (\array_key_exists($isVisible, $transientKeys)) {
                $approve = \get_transient($transientKeys[$isVisible]);
                $params = $request->get_params();
                $filterParams = ['page', 'rating', 'date', 'reviewer', 'search', 'product', 'category', 'oldest_first', 'newest_first'];
                if (\array_intersect_key(\array_flip($filterParams), $params)) {
                    $resp = $this->reviewService->reviewList($params);
                    return $this->enrichReviewList($resp);
                } elseif ($approve) {
                    $response = ['count' => $approve['count'], 'reviews' => $approve['reviews'], 'meta' => $approve['meta']];
                    return Helper::rest($response)->success("Success");
                } else {
                    $this->adminAllReviewSaasCall($params);
                }
            }
            if (empty($request->get_params())) {
                $data = \get_transient('rvx_reviews_data_list');
                if ($data) {
                    $response = ['count' => $data['count'], 'reviews' => $data['reviews'], 'meta' => $data['meta']];
                    return Helper::rest($response)->success("Success");
                } else {
                    $resp = $this->reviewService->reviewList($request->get_params());
                    if ($resp->getStatusCode() === Response::HTTP_OK && empty($request->get_params())) {
                        $this->reviewListStoreInDB($resp->getApiData());
                    }
                    return $this->enrichReviewList($resp);
                }
            } else {
                $resp = $this->reviewService->reviewList($request->get_params());
                return $this->enrichReviewList($resp);
            }
        } catch (Exception $e) {
            \error_log("Error fetching review list: " . $e->getMessage());
        }
    }
    public function visibilityPaginationSaasCall($request, $differentReview)
    {
        if ($differentReview === \true) {
            $this->adminAllReviewSaasCall($request->get_params());
        }
    }
    public function reviewListStoreInDB($reviewData)
    {
        \delete_transient('rvx_reviews_data_list');
        set_transient('rvx_reviews_data_list', $reviewData, 3600);
    }
    public function storeVisibilityReview($data, $visibility)
    {
        if ($visibility === 'published') {
            \delete_transient('rvx_review_approve_data');
            set_transient('rvx_review_approve_data', $data, 3600);
        }
        if ($visibility === 'pending') {
            \delete_transient('rvx_review_pending_data');
            set_transient('rvx_review_pending_data', $data, 3600);
        }
        if ($visibility === 'spam') {
            \delete_transient('rvx_review_spam_data');
            set_transient('rvx_review_spam_data', $data, 3600);
        }
        if ($visibility === 'trash') {
            \delete_transient('rvx_review_trash_data');
            set_transient('rvx_review_trash_data', $data, 3600);
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function show($request)
    {
        $resp = $this->reviewService->getReview($request);
        return Helper::getApiResponse($resp);
    }
    /**
     * @param $request
     * @return Response
     */
    public function store($request)
    {
        try {
            // Temporarily disable comment notification emails
            remove_action('comment_post', 'wp_notify_postauthor');
            add_filter('comments_notify', '__return_false');
            $resp = $this->reviewService->createReview($request);
            // Re-enable the comment notification emails
            add_action('comment_post', 'wp_notify_postauthor');
            remove_filter('comments_notify', '__return_false');
            // Clear caches
            $this->cacheServices->removeCache();
            $postId = $request->get_param('wp_post_id');
            if ($postId) {
                $this->cacheServices->removeProductCache($postId);
            }
            return Helper::getApiResponse($resp);
        } catch (Exception $e) {
            // Re-enable the comment notification emails in case of error
            add_action('comment_post', 'wp_notify_postauthor');
            remove_filter('comments_notify', '__return_false');
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Not Create', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function update($request)
    {
        try {
            $resp = $this->reviewService->updateReview($request);
            if ($resp->getStatusCode() >= 200 && $resp->getStatusCode() < 300) {
                // 1. Clear site-wide generic caches
                $this->cacheServices->removeCache();
                // 2. Clear product-specific caches (Insight & Latest Reviews)
                $postId = $request->get_param('wp_post_id');
                if ($postId) {
                    \delete_transient("rvx_{$postId}_latest_reviews_insight");
                    \delete_transient("rvx_{$postId}_latest_reviews");
                }
            }
            return Helper::getApiResponse($resp);
        } catch (Exception $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Update Failed', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function delete($request)
    {
        try {
            $resp = $this->reviewService->deleteReview($request);
            $this->cacheServices->removeCache();
            return $resp;
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Visibility Change', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function restoreReview($request)
    {
        try {
            $response = $this->reviewService->restoreReview($request);
            $this->cacheServices->removeCache();
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Visibility Change', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function verify($request)
    {
        try {
            $resp = $this->reviewService->isVerify($request);
            $this->cacheServices->removeCache();
            return $resp;
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Visibility Not Change', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function visibility($request)
    {
        try {
            $response = $this->reviewService->isvisibility($request);
            $this->cacheServices->removeCache();
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Visibility Not Change', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function updateReqEmail($request)
    {
        try {
            $resp = $this->reviewService->updateReqEmail($request);
            $this->cacheServices->removeCache();
            return $resp;
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function replies($request)
    {
        try {
            $resp = $this->reviewService->reviewReplies($request);
            return $resp;
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Reply Fails', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function repliesUpdate($request)
    {
        try {
            $resp = $this->reviewService->reviewRepliesUpdate($request);
            return Helper::rvxApi($resp)->success('Review Reply Updated');
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Reply Updated Fails', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function replyDelete($request)
    {
        try {
            $resp = $this->reviewService->reviewRepliesDelete($request);
            return Helper::getApiResponse($resp);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function aiReview($request)
    {
        try {
            // Temporarily disable comment notification emails
            remove_action('comment_post', 'wp_notify_postauthor');
            add_filter('comments_notify', '__return_false');
            $resp = $this->reviewService->aiReview($request);
            // Re-enable the comment notification emails
            add_action('comment_post', 'wp_notify_postauthor');
            remove_filter('comments_notify', '__return_false');
            return Helper::getApiResponse($resp);
        } catch (Throwable $e) {
            // Re-enable the comment notification emails in case of error
            add_action('comment_post', 'wp_notify_postauthor');
            remove_filter('comments_notify', '__return_false');
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function aiReviewCount()
    {
        try {
            $resp = $this->reviewService->aiReviewCount();
            return Helper::getApiResponse($resp);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Ai Review Count', $e->getCode());
        }
    }
    public function aggregationMeta($request)
    {
        try {
            $response = $this->reviewService->aggregationMeta($request);
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function reviewBulkUpdate($request)
    {
        try {
            $response = $this->reviewService->reviewBulkUpdate($request->get_params());
            $this->cacheServices->removeCache();
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function reviewBulkTrash($request)
    {
        try {
            $response = $this->reviewService->reviewBulkTrash($request->get_params());
            $this->cacheServices->removeCache();
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function reviewEmptyTrash($request)
    {
        try {
            $response = $this->reviewService->reviewEmptyTrash($request->get_params());
            $this->cacheServices->removeCache();
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Empty Fails', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function restoreTrashItem($request)
    {
        //Bulk trash
        try {
            $response = $this->reviewService->restoreTrashItem($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     *
     * @return Response
     */
    public function reviewAggregation()
    {
        try {
            $resp = $this->reviewService->reviewAggregation();
            // dd($resp->getApiData());
            return Helper::getApiResponse($resp);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Aggregation Fails', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function reviewMoveToTrash($request)
    {
        try {
            $response = $this->reviewService->reviewMoveToTrash($request->get_params());
            $this->cacheServices->removeCache();
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Move to trash Fails', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function highlight($request)
    {
        try {
            $response = $this->reviewService->highlight($request->get_params());
            $this->cacheServices->removeCache();
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails(__('Review Highlight', 'reviewx'), $e->getCode());
        }
    }
    public function bulkTenReviews($request)
    {
        try {
            $response = $this->reviewService->bulkTenReviews($request->get_params());
            $this->cacheServices->removeCache();
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails(__('Latest ten reviews fails', 'reviewx'), $e->getCode());
        }
    }
    public function bulkActionProductMeta($request)
    {
        try {
            foreach ($request->get_params() as $item) {
                if (!Helper::arrayGet($item, 'product_wp_id')) {
                    return "No product found";
                }
                $reviewAndMeta = ['reviews' => Helper::arrayGet($item, 'reviews'), 'meta' => Helper::arrayGet($item, 'meta')];
                $latest_ten_review = \json_encode($reviewAndMeta, \true);
                set_transient("rvx_{$item['product_wp_id']}_latest_reviews", $latest_ten_review, 604800);
                // Expires in 7 days
                return Helper::rest()->success("Success");
            }
        } catch (Exception $e) {
            \error_log($e->getMessage());
            return Helper::rest($e->getMessage())->fails("Fail");
        }
    }
    /**
     *
     * @return Response
     */
    public function reviewListMultiCriteria()
    {
        $resp = $this->reviewService->reviewListMultiCriteria();
        return Helper::getApiResponse($resp);
    }
    /**
     * @param $request
     * @return Response
     */
    public function getSingleProductAllReviews($request)
    {
        try {
            $resp = $this->reviewService->getSingleProductAllReviews($request);
            return Helper::getApiResponse($resp);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('failed', $e->getCode());
        }
    }
}

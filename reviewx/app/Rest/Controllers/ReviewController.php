<?php

namespace Rvx\Rest\Controllers;

use Exception;
use Throwable;
use Rvx\WPDrill\Response;
use Rvx\Services\ReviewService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\Utilities\Helper;
class ReviewController implements InvokableContract
{
    protected ReviewService $reviewService;
    /**
     *
     */
    public function __construct()
    {
        $this->reviewService = new ReviewService();
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
        $resp = $this->reviewService->getReviews($request->get_params());
        return Helper::getApiResponse($resp);
    }
    public function reviewList($request)
    {
        $resp = $this->reviewService->reviewList($request->get_params());
        return Helper::getApiResponse($resp);
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
            return Helper::getApiResponse($resp);
        } catch (Exception $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Not Create', $e->getCode());
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
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Replay Fails', $e->getCode());
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
            return Helper::getApiResponse($resp);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Replay Updated Fails', $e->getCode());
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
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
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
        $resp = $this->reviewService->reviewAggregation();
        return Helper::getApiResponse($resp);
    }
    /**
     * @param $request
     * @return Response
     */
    public function reviewMoveToTrash($request)
    {
        try {
            $response = $this->reviewService->reviewMoveToTrash($request->get_params());
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
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails(__('Review Highlight', 'reviewx'), $e->getCode());
        }
    }
    public function bulkTenReviews($request)
    {
        try {
            $response = $this->reviewService->bulkTenReviews($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails(__('Review Highlight', 'reviewx'), $e->getCode());
        }
    }
    public function bulkActionProductMeta($request)
    {
        try {
            foreach ($request->get_params() as $item) {
                if (!Helper::arrayGet($item, 'product_wp_id')) {
                    return "No prodduct found";
                }
                $reviewAndMeta = ['reviews' => Helper::arrayGet($item, 'reviews'), 'meta' => Helper::arrayGet($item, 'meta')];
                $latest_ten_review = \json_encode($reviewAndMeta, \true);
                update_post_meta($item['product_wp_id'], '_rvx_latest_reviews', $latest_ten_review);
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

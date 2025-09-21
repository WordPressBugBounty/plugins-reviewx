<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
use Exception;
use Rvx\Models\Post;
use Rvx\Utilities\Auth\Client;
class ReviewsApi extends \Rvx\Api\BaseApi
{
    public function getReviews($data) : Response
    {
        if (!empty($data)) {
            \parse_str($data, $query_params);
            if (isset($query_params['product']) && \is_numeric($query_params['product'])) {
                $query_params['product'] = Client::getUid() . '-' . $query_params['product'];
            }
            return $this->get('reviews?' . \http_build_query($query_params));
        }
        return $this->get('reviews');
    }
    public function reviewList($data) : Response
    {
        if (!empty($data)) {
            \parse_str($data, $query_params);
            if (isset($query_params['product']) && \is_numeric($query_params['product'])) {
                $query_params['product'] = Client::getUid() . '-' . $query_params['product'];
            }
            return $this->get('reviews/list?' . \http_build_query($query_params));
        }
        return $this->get('reviews/list');
    }
    /**
     * @param $id
     * @return Response
     * @throws Exception
     */
    public function getReview($wpUniqueId) : Response
    {
        return $this->get('reviews/' . $wpUniqueId);
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function create(array $data) : Response
    {
        return $this->withJson($data)->post('reviews/create/manual');
    }
    /**
     * @param array $data
     * @param $wpUniqueId
     * @return Response
     * @throws Exception
     */
    public function updateReviewData(array $data, $wpUniqueId) : Response
    {
        return $this->withJson($data)->put('reviews/' . $wpUniqueId . '/update');
    }
    public function updateWooReviewData(array $data, $wpUniqueId) : Response
    {
        return $this->withJson($data)->put('reviews/' . $wpUniqueId . '/update?woocommerce_update=true');
    }
    /**
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function deleteReviewData($wpUniqueId) : Response
    {
        return $this->delete('reviews/' . $wpUniqueId . '/delete');
    }
    /**
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function restoreReview($wpUniqueId) : Response
    {
        return $this->put('reviews/' . $wpUniqueId . '/restore');
    }
    /**
     * @param $data
     * @param $wpUniqueId
     * @return Response
     * @throws Exception
     */
    public function verifyReview($data, $wpUniqueId) : Response
    {
        return $this->put('reviews/' . $wpUniqueId . '/verify?status=' . $data);
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function visibilityReviewData(array $data, $wpUniqueId) : Response
    {
        if ($data['status']) {
            return $this->put('reviews/' . $wpUniqueId . '/visibility?status=' . $data['status']);
        }
        return $this->put('reviews/' . $wpUniqueId . '/visibility');
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function sendUpdateReviewRequestEmail(array $data, $wpUniqueId) : Response
    {
        return $this->withJson($data)->put('reviews/' . $wpUniqueId . '/send/update/request-email');
    }
    /**
     * @return Response
     * @throws Exception
     */
    public function reviewAggregation() : Response
    {
        return $this->get('reviews/get-aggregation');
    }
    /**
     * @return Response
     * @throws Exception
     */
    public function getSpecificReviewItem($uid, $data) : Response
    {
        return $this->withJson($data)->post('storefront/' . $uid . '/widgets/short/code/reviews');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function bulkStatusUpdateReviewData(array $data) : Response
    {
        return $this->withJson($data)->put('reviews/bulk/status/update');
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function replayCommentReviewData(array $data, $uid) : Response
    {
        return $this->withJson($data)->post('reviews/{$uid}/comments');
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function updateReplayCommentReviewData(array $data, $uid) : Response
    {
        return $this->withJson($data)->post('reviews/{$uid}/update/comments');
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function deleteReplayCommentReviewData(array $data, $uid) : Response
    {
        return $this->withJson($data)->post('reviews/{$uid}/delete/{$id}/comments');
    }
    //replies
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function commentReply(array $data, $wpUniqueId) : Response
    {
        return $this->withJson($data)->post('reviews/' . $wpUniqueId . '/replies');
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function updateCommentReply(array $data, $uid) : Response
    {
        return $this->withJson($data)->put('reviews/' . $uid . '/update/replies');
    }
    /**
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function deleteCommentReply($wpUniqueId) : Response
    {
        return $this->delete('reviews/' . $wpUniqueId . '/delete/reply');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function aiReview(array $data) : Response
    {
        return $this->withJson($data)->post('reviews/create/ai');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function aiReviewCount() : Response
    {
        return $this->get('reviews/ai/count');
    }
    /**
     * @param $request
     * @return Response
     * @throws Exception
     */
    public function getWidgetReviewsForProductApi($request) : Response
    {
        $query_string = \http_build_query($request->get_params());
        \parse_str($query_string, $query_params);
        unset($query_params['product_id']);
        $productId = $request->get_param('product_id');
        $siteUid = Client::getUid();
        $post = Post::find($productId);
        $productWpUniqueId = $siteUid . '-' . $post->ID;
        $new_query_string = \http_build_query($query_params);
        $callableRoute = "/storefront/{$siteUid}/widgets/products/{$productWpUniqueId}/reviews";
        if (!empty($new_query_string)) {
            $callableRoute .= '?' . $new_query_string;
        }
        return $this->get($callableRoute);
    }
    /**
     * @param $request
     * @return Response
     * @throws Exception
     */
    public function getWidgetAllReviewsForSiteApi($request, $site_id) : Response
    {
        $query_params = $request->get_params();
        unset($query_params['site_id']);
        $callableRoute = "/storefront/{$site_id}/all/reviews/shortcode";
        $query_string = \http_build_query($query_params);
        if ($query_string) {
            $callableRoute .= '?' . $query_string;
        }
        return $this->get($callableRoute);
    }
    /**
     * @param $request
     * @return Response
     * @throws Exception
     */
    public function getWidgetReviewsListShortcodeApi($request) : Response
    {
        $query_params = $request->get_params();
        unset($query_params['product_id']);
        $productId = $request->get_param('product_id');
        $siteUid = Client::getUid();
        $post = Post::find($productId);
        $productWpUniqueId = $siteUid . '-' . $post->ID;
        $query_string = \http_build_query($query_params);
        $callableRoute = "/storefront/{$siteUid}/widgets/products/{$productWpUniqueId}/reviews/shortcode";
        if ($query_string) {
            $callableRoute .= '?' . $query_string;
        }
        return $this->get($callableRoute);
    }
    public function getSingleProductAllReviews($productId) : Response
    {
        $siteUid = Client::getUid();
        $post = Post::find($productId);
        $productWpUniqueId = $siteUid . '-' . $post->ID;
        $callableRoute = "/storefront/{$siteUid}/widgets/products/{$productWpUniqueId}/reviews";
        return $this->get($callableRoute);
    }
    /**
     * @param $request
     * @return Response
     * @throws Exception
     */
    public function getWidgetInsight($request) : Response
    {
        $query_string = \http_build_query($request->get_params());
        \parse_str($query_string, $query_params);
        unset($query_params['product_id']);
        $productId = $request->get_param('product_id');
        $siteUid = Client::getUid();
        $post = Post::find($productId);
        $productWpUniqueId = $siteUid . '-' . $post->ID;
        $new_query_string = \http_build_query($query_params);
        $callableRoute = "/storefront/{$siteUid}/widgets/products/{$productWpUniqueId}/insight";
        if (!empty($new_query_string)) {
            $callableRoute .= '?' . $new_query_string;
        }
        return $this->get($callableRoute);
    }
    public function reviewBulkUpdate($data)
    {
        return $this->withJson($data)->post('/reviews/bulk/status/update');
    }
    public function restoreTrashItem($data)
    {
        return $this->withJson($data)->post('reviews/bulk/restore/trash');
    }
    public function reviewBulkTrash($data)
    {
        return $this->withJson($data)->post('/reviews/bulk/trash');
    }
    public function reviewEmptyTrash()
    {
        return $this->delete('reviews/empty/trash');
    }
    /**
     * @param $request
     * @return Response
     * @throws Exception
     */
    public function saveWidgetReviewsForProductApi($data) : Response
    {
        $callableRoute = "/storefront/reviews";
        return $this->withJson($data)->post($callableRoute);
    }
    /**
     * @param $request
     * @return Response
     * @throws Exception
     */
    public function reviewMoveToTrash($data) : Response
    {
        $callableRoute = "reviews/" . $data['WpUniqueId'] . "/trash";
        return $this->put($callableRoute);
    }
    /**
     * @param $request
     * @return Response
     * @throws Exception
     */
    public function likeDIslikePreference($data) : Response
    {
        $callableRoute = 'storefront/reviews/' . $data['uniq_id'] . '/preference';
        return $this->withJson($data)->post($callableRoute);
    }
    /**
     * @param $request
     * @return Response
     * @throws Exception
     */
    public function reviewListMultiCriteria() : Response
    {
        return $this->get('reviews/list/multi-criteria');
    }
    /**
     * @param $request
     * @return Response
     * @throws Exception
     */
    public function getProductReviewFormId($data) : Response
    {
        if ($data['product'] != null) {
            $siteUid = Client::getUid();
            $post = Post::find($data['product']);
            $productWpUniqueId = $siteUid . '-' . $post->ID;
            $callableRoute = "/storefront/{$siteUid}/widgets/products/{$productWpUniqueId}/reviews";
            return $this->get($callableRoute);
        }
        return $this->get('reviews');
    }
    /**
     * @param $request
     * @return Response
     * @throws Exception
     */
    public function highlight($data) : Response
    {
        $callableRoute = 'reviews/' . $data['wpUniqueId'] . '/highlight';
        return $this->withJson($data)->put($callableRoute);
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function reviewRequestStoreItem(array $data, $uid) : Response
    {
        return $this->withJson($data)->post('storefront/request-review/email/' . $uid . '/store/items');
    }
}

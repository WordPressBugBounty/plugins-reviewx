<?php

namespace ReviewX\Services\Api;

\defined("ABSPATH") || exit;
use ReviewX\Api\AuthApi;
use ReviewX\Services\Service;
class LoginService extends Service
{
    public function resetPostMeta()
    {
        $insight_key = '_rvx_latest_reviews_insight';
        $review_key = '_rvx_latest_reviews';
        \delete_metadata('post', 0, $insight_key, '', \true);
        \delete_metadata('post', 0, $review_key, '', \true);
        (new \ReviewX\Services\CacheServices())->removeCache();
    }
    public function resetProductWisePostMeta($product_id)
    {
        if (empty($product_id) || !\is_numeric($product_id)) {
            return;
        }
        \delete_post_meta($product_id, '_rvx_latest_reviews');
    }
    public function forgetPassword($data)
    {
        return (new AuthApi())->forgetPassword($data);
    }
    public function resetPassword($data)
    {
        return (new AuthApi())->resetPassword($data);
    }
}

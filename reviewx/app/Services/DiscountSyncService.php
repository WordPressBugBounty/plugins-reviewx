<?php

namespace Rvx\Services;

use Exception;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
class DiscountSyncService extends \Rvx\Services\Service
{
    protected $discountCount = 0;
    protected $usedbyRelation;
    protected $maximumAmountRelation;
    protected $minimumAmountRelation;
    protected $freeShippingRelation;
    protected $dateExpiresRelation;
    protected $usageLimitPerUserRelation;
    protected $usageLimitRelation;
    protected $couponAmountRelation;
    protected $discountTypeRelation;
    public function processDiscountForSync($file) : int
    {
        $this->syncPostMeta();
        return $this->syncPost($file);
    }
    public function syncPostMeta()
    {
        try {
            $this->usedbyRelation = [];
            $this->maximumAmountRelation = [];
            $this->minimumAmountRelation = [];
            $this->freeShippingRelation = [];
            $this->dateExpiresRelation = [];
            $this->usageLimitPerUserRelation = [];
            $this->usageLimitRelation = [];
            $this->couponAmountRelation = [];
            $this->discountTypeRelation = [];
            DB::table('postmeta')->whereIn('meta_key', ['_used_by', 'maximum_amount', 'minimum_amount', 'free_shipping', 'date_expires', 'usage_limit_per_user', 'usage_limit', 'coupon_amount', 'discount_type'])->chunk(100, function ($allPostMeta) {
                foreach ($allPostMeta as $postMetas) {
                    switch ($postMetas->meta_key) {
                        case '_used_by':
                            $this->usedbyRelation[$postMetas->post_id] = $postMetas->meta_value;
                            break;
                        case 'maximum_amount':
                            $this->maximumAmountRelation[$postMetas->post_id] = $postMetas->meta_value;
                            break;
                        case 'minimum_amount':
                            $this->minimumAmountRelation[$postMetas->post_id] = $postMetas->meta_value;
                            break;
                        case 'free_shipping':
                            $this->freeShippingRelation[$postMetas->post_id] = $postMetas->meta_value;
                            break;
                        case 'date_expires':
                            $this->dateExpiresRelation[$postMetas->post_id] = $postMetas->meta_value;
                            break;
                        case 'usage_limit_per_user':
                            $this->usageLimitPerUserRelation[$postMetas->post_id] = $postMetas->meta_value;
                            break;
                        case 'usage_limit':
                            $this->usageLimitRelation[$postMetas->post_id] = $postMetas->meta_value;
                            break;
                        case 'coupon_amount':
                            $this->couponAmountRelation[$postMetas->post_id] = $postMetas->meta_value;
                            break;
                        case 'discount_type':
                            $this->discountTypeRelation[$postMetas->post_id] = $postMetas->meta_value;
                            break;
                    }
                }
            });
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function syncPost($file)
    {
        $discountCount = 0;
        DB::table('posts')->select(['ID', 'post_type', 'post_title', 'post_name', 'post_status', 'post_modified'])->orderBy('ID')->whereIn('post_type', ['shop_coupon'])->chunk(100, function ($discounts) use(&$file, &$discountCount) {
            foreach ($discounts as $discount) {
                $formatedDiscount = $this->processDiscount($discount);
                if ($formatedDiscount['single_code'] && $formatedDiscount['type']) {
                    Helper::appendToJsonl($file, $formatedDiscount);
                    $discountCount++;
                }
            }
        });
        Helper::rvxLog($discountCount, "Cuopn Done");
        return $discountCount;
    }
    public function processDiscount($discount) : array
    {
        $start_date = \wp_date('Y-m-d', \strtotime($discount->post_modified)) ?? null;
        $end_date = \wp_date('Y-m-d', $this->dateExpiresRelation[$discount->ID]) ?? null;
        $exper_day = $this->discountExperDay($start_date, $end_date);
        return ['rid' => 'rid://Discount/' . (int) $discount->ID, "request_type" => 2, "wp_id" => (int) $discount->ID, "wp_unique_id" => Client::getUid() . '-' . (int) $discount->ID, "site_id" => Client::getSiteId(), "code_type" => 1, "status" => $this->discountStatus($discount->post_status), "single_code" => !empty($discount->post_title) ? $discount->post_title : null, "title" => !empty($discount->post_title) ? $discount->post_title : null, "type" => $this->discountType($this->discountTypeRelation[$discount->ID]) ?? null, "value" => 6, "free_shipping" => $this->dataTypeConvert($this->freeShippingRelation[$discount->ID]) ?? \false, "can_be_used_with_other_coupon" => \false, "exclude_sale_items_from_discount" => \true, "minimum_amount" => Helper::formatToTwoDecimalPlaces($this->minimumAmountRelation[$discount->ID] ?? 0), "maximum_amount" => Helper::formatToTwoDecimalPlaces($this->maximumAmountRelation[$discount->ID] ?? 0), "usage_limit_per_coupon" => (int) $this->usageLimitPerUserRelation[$discount->ID] ?? 0, "usage_limit_per_user" => (int) $this->usageLimitRelation[$discount->ID] ?? 0, "start_date" => $start_date, "start_time" => \wp_date('H:i', \strtotime($discount->post_modified)) ?? null, "expires_in" => $exper_day, "end_date" => $end_date, "end_time" => \wp_date('H:i', $this->dateExpiresRelation[$discount->ID]) ?? null];
    }
    public function discountStatus($status) : int
    {
        switch ($status) {
            case 'publish':
                return 1;
            case 'private':
                return 2;
            default:
                return 3;
        }
    }
    public function discountType($type) : string
    {
        switch ($type) {
            case 'fixed_cart':
                return 'fixed_amount';
            case 'percent':
                return 'percentage';
            default:
                return '';
        }
    }
    public function dataTypeConvert($data) : bool
    {
        if ($data === 'yes') {
            return \true;
        }
        return \false;
    }
    public function discountExperDay($start_date, $end_date)
    {
        if ($start_date && $end_date) {
            $start_timestamp = \strtotime($start_date);
            $end_timestamp = \strtotime($end_date);
            $difference_in_seconds = $end_timestamp - $start_timestamp;
            return \floor($difference_in_seconds / (60 * 60 * 24));
        }
        return null;
    }
}

<?php

namespace Rvx\Services;

use WC_Coupon;
use Exception;
use Rvx\Api\DiscountApi;
class DiscountService extends \Rvx\Services\Service
{
    public function wpDiscountCreate($data)
    {
        $coupon = $this->setBasicCouponData($data);
        $this->setAdditionalCouponData($coupon, $data);
        $coupon->save();
        return $coupon;
    }
    private function setBasicCouponData($data)
    {
        $coupon_data = ['code' => sanitize_text_field($data['code']), 'discount_type' => sanitize_text_field($data['discount_type']), 'amount' => sanitize_text_field($data['amount'])];
        $coupon = new WC_Coupon();
        $coupon->set_props($coupon_data);
        return $coupon;
    }
    private function setAdditionalCouponData($coupon, $data)
    {
        if (isset($data['expiry_date'])) {
            $coupon->set_date_expires(sanitize_text_field($data['expiry_date']));
        }
        if (isset($data['individual_use'])) {
            $coupon->set_individual_use($data['individual_use'] === 'yes');
        }
        if (isset($data['usage_limit'])) {
            $coupon->set_usage_limit(sanitize_text_field($data['usage_limit']));
        }
        if (isset($data['free_shipping'])) {
            $coupon->set_free_shipping($data['free_shipping'] === 'yes');
        }
        if (isset($data['usage_limit_per_user'])) {
            $coupon->set_usage_limit_per_user((int) $data['usage_limit_per_user']);
        }
        if (isset($data['minimum_amount'])) {
            $coupon->add_meta_data('minimum_amount', sanitize_text_field($data['minimum_amount']), \true);
        }
        if (isset($data['maximum_amount'])) {
            $coupon->add_meta_data('maximum_amount', sanitize_text_field($data['maximum_amount']), \true);
        }
    }
    public function deleteDiscount($couponId)
    {
        try {
            $coupon = new WC_Coupon($couponId);
            $coupon->delete(\true);
            // true for force delete
            return \true;
        } catch (Exception $e) {
            return \false;
        }
    }
    public function getDiscount()
    {
        return (new DiscountApi())->getDiscount();
    }
    public function discountSetting()
    {
        return (new DiscountApi())->discountSetting();
    }
    public function discountSettingsSave($data)
    {
        return (new DiscountApi())->discountSettingsSave($data);
    }
    public function saveDiscount($data)
    {
        return (new DiscountApi())->saveDiscount($data);
    }
    public function discountTemplateGet()
    {
        return (new DiscountApi())->discountTemplateGet();
    }
    public function discountTemplatePost($data)
    {
        return (new DiscountApi())->discountTemplatePost($data);
    }
    public function discountMessage($data)
    {
        return (new DiscountApi())->discountMessage($data);
    }
}

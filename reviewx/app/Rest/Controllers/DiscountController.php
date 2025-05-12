<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\DiscountService;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Throwable;
use Rvx\WPDrill\Response;
class DiscountController
{
    protected $couponService;
    /**
     * @param DiscountService $couponService
     */
    public function __construct(DiscountService $couponService)
    {
        $this->couponService = $couponService;
    }
    /**
     * @param $request
     * @return Response
     */
    public function wpDiscountCreate($request) : Response
    {
        try {
            $coupon = $this->couponService->wpDiscountCreate($request->get_params());
            if (!$coupon) {
                return Helper::rvxApi()->success('Failed to create discount');
            }
            $couponData = $coupon->get_data();
            $couponId = Helper::arrayGet($couponData, 'id');
            $payload = ['wp_id' => $couponId, 'wp_unique_id' => Client::getUid() . '-' . $couponId, 'code_type' => $request->get_param('code_type'), 'status' => 1, 'site_id' => Client::getSiteId(), 'request_type' => $request->get_param('request_type'), 'type' => $request->get_param('discount_type') === 'percent' ? 'percentage' : 'fixed_amount', 'value' => Helper::arrayGet($couponData, 'amount'), 'usage_limit_per_user' => $request->get_param('usage_limit_per_user') ?? 0, 'usage_limit_per_coupon' => $request->get_param('usage_limit') ?? 0, 'minimum_amount' => (int) Helper::arrayGet($couponData, 'minimum_amount'), 'maximum_amount' => (int) Helper::arrayGet($couponData, 'maximum_amount'), 'exclude_sale_items_from_discount' => $request->get_param('exclude_sale_items_from_discount'), 'free_shipping' => $request->get_param('free_shipping'), 'can_be_used_with_other_coupon' => $request->get_param('individual_use'), 'single_code' => Helper::arrayGet($couponData, 'code'), 'start_date' => $request->get_param('start_date') ?? \date('Y-m-d'), 'start_time' => $request->get_param('start_time'), 'expires_in' => $request->get_param('expires_in'), 'end_date' => $request->get_param('expiry_date'), 'end_time' => $request->get_param('end_time')];
            $resp = $this->couponService->saveDiscount($payload);
            if ($resp->getStatusCode() !== Response::HTTP_OK) {
                // Delete the created coupon if API request failed
                $this->couponService->deleteDiscount($couponId);
                return Helper::rvxApi()->fails('Failed to save discount to API');
            }
            return Helper::getApiResponse($resp);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Coupon Create Fails', $e->getCode());
        }
    }
    /**
     * @return Response
     */
    public function getDiscount()
    {
        $resp = $this->couponService->getDiscount();
        return Helper::getApiResponse($resp);
    }
    /**
     * @return Response
     */
    public function discountSetting()
    {
        $resp = $this->couponService->discountSetting();
        return Helper::getApiResponse($resp);
    }
    public function discountSettingsSave($request)
    {
        try {
            $response = $this->couponService->discountSettingsSave($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Coupon Create Fails', $e->getCode());
        }
    }
    public function saveDiscount($request)
    {
        try {
            $response = $this->couponService->saveDiscount($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Coupon Create Fails', $e->getCode());
        }
    }
    public function discountTemplateGet()
    {
        try {
            $response = $this->couponService->discountTemplateGet();
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Coupon Template Get Fails', $e->getCode());
        }
    }
    public function discountTemplatePost($request)
    {
        try {
            $response = $this->couponService->discountTemplatePost($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Coupon Template Fails', $e->getCode());
        }
    }
    public function discountMessage($request)
    {
        try {
            $response = $this->couponService->discountMessage($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Discount Message', $e->getCode());
        }
    }
}

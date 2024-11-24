<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\DiscountService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Helper;
class DiscountController implements InvokableContract
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
     * @return void
     */
    public function __invoke()
    {
    }
    /**
     * @return Response
     */
    public function wpDiscountCreate($request)
    {
        try {
            $coupon = $this->couponService->wpDiscountCreate($request->get_params());
            return Helper::rvxApi($coupon->get_data())->success('Discount Create successfully');
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Coupon Create Fails', $e->getCode());
        }
    }
    public function saveDiscount($request)
    {
        try {
            $response = $this->couponService->saveDiscount($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Coupon Create Fails', $e->getCode());
        }
    }
    public function discountTemplateGet()
    {
        try {
            $response = $this->couponService->discountTemplateGet();
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Coupon Template Get Fails', $e->getCode());
        }
    }
    public function discountTemplatePost($request)
    {
        try {
            $response = $this->couponService->discountTemplatePost($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Coupon Template Fails', $e->getCode());
        }
    }
    public function discountMessage($request)
    {
        try {
            $response = $this->couponService->discountMessage($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Discount Message', $e->getCode());
        }
    }
}

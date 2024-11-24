<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\CustomService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Helper;
class CustomPostController implements InvokableContract
{
    protected $customService;
    /**
     * @param CustomService $customService
     */
    public function __construct(CustomService $customService)
    {
        $this->customService = $customService;
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
    public function customGet()
    {
        $resp = $this->customService->customGet();
        $this->customPostypeSetting($resp);
        return Helper::getApiResponse($resp);
    }
    /**
     * @return Response
     */
    public function customStore($request)
    {
        try {
            $response = $this->customService->customStore($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     * @return Response
     */
    public function customUpdate($request)
    {
        try {
            $response = $this->customService->customUpdate($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     * @return Response
     */
    public function customdelete($request)
    {
        try {
            $response = $this->customService->customdelete($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     * @return Response
     */
    public function customWpGet($request)
    {
        try {
            $args = array('public' => \true, '_builtin' => \false);
            $post_types = get_post_types($args, 'objects');
            $result = array();
            if (!empty($post_types)) {
                foreach ($post_types as $post_type) {
                    $result[] = array('name' => $post_type->labels->name, 'slug' => $post_type->name);
                }
            }
            return Helper::rvxApi($result)->success();
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    /**
     * @return Response
     */
    public function customPostStatusChange($request)
    {
        try {
            $response = $this->customService->customPostStatusChange($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Review Bulk Fails', $e->getCode());
        }
    }
    public function customPostypeSetting($response)
    {
        $dataArray = \json_decode($response, \true);
        if ($dataArray !== null) {
            update_option('_rvx_custom_post_type_settings', $dataArray);
        }
    }
}

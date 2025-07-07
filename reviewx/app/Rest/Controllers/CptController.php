<?php

namespace Rvx\Rest\Controllers;

use Rvx\CPT\CptHelper;
use Rvx\Services\CptService;
use Rvx\Services\DataSyncService;
use Rvx\Services\SettingService;
use Rvx\Utilities\Helper;
use Rvx\Services\CacheServices;
use Throwable;
use Rvx\WPDrill\Response;
class CptController
{
    protected CptService $cptService;
    protected DataSyncService $dataSyncService;
    protected CacheServices $cacheServices;
    protected CptHelper $cptHelper;
    public function __construct()
    {
        $this->cptService = new CptService();
        $this->dataSyncService = new DataSyncService();
        $this->cacheServices = new CacheServices();
        $this->cptHelper = new CptHelper();
    }
    /**
     * @return array
     */
    public function cptGetOnSync()
    {
        $response = $this->cptService->cptGet();
        $status = $this->cptSettings($response);
        return [$status, \json_decode($response, \true)];
    }
    /**
     * @return Response
     */
    public function cptGet()
    {
        $response = $this->cptService->cptGet();
        $this->cptSettings($response);
        return Helper::getApiResponse($response);
    }
    /**
     * @return Response
     */
    public function cptStore($request)
    {
        try {
            $response = $this->cptService->cptStore($request->get_params());
            $resData = Helper::saasResponse($response);
            if ($response->getStatusCode() === 200) {
                // Update (_rvx_cpt_settings) CPT Settings
                $response = $this->cptService->cptGet();
                $this->cptSettings($response);
                // Update (_rvx_ettings_{post_type}) CPT Settings
                $post_type = $resData->data['data']['post_type'] ? \strtolower($resData->data['data']['post_type']) : '';
                if (!empty($post_type)) {
                    $review_response = (new \Rvx\Rest\Controllers\SettingController())->getApiReviewSettingsOnSync($post_type);
                    // Update Review settings
                    $review_settings = $review_response['data']['review_settings'];
                    (new SettingService())->updateReviewSettingsOnSync($review_settings, \strtolower($post_type));
                }
                // Upload CPT data to Saas
                $this->dataSyncService->dataSync('default', $post_type);
                $this->cacheServices->removeCache();
            }
            return Helper::rvxApi($resData)->success('Create Success', 200);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Create Fails', $e->getCode());
        }
    }
    /**
     * @return Response
     */
    public function cptUpdate($request)
    {
        try {
            $response = $this->cptService->cptUpdate($request->get_params());
            $resData = Helper::saasResponse($response);
            if ($response->getStatusCode() === 200) {
                // Update (_rvx_cpt_settings) CPT Settings
                $response = $this->cptService->cptGet();
                $this->cptSettings($response);
            }
            return Helper::rvxApi($resData)->success('Update Success', 200);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Update Fails', $e->getCode());
        }
    }
    /**
     * @return Response
     */
    public function cptDelete($request)
    {
        try {
            $response = $this->cptService->cptDelete($request->get_params());
            $resData = Helper::saasResponse($response);
            if ($response->getStatusCode() === 200) {
                // Update (_rvx_cpt_settings) CPT Settings
                $response = $this->cptService->cptGet();
                $this->cptSettings($response);
            }
            return Helper::rvxApi($resData)->success('Delete Success', 200);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Delete Fails', $e->getCode());
        }
    }
    public function customPostTypes($request)
    {
        try {
            // Available post types
            $cptList = \array_map(function ($cpt) {
                return ['name' => \strtolower($cpt['name']), 'slug' => \strtolower($cpt['slug'])];
            }, $this->cptHelper->getPublicCptList());
            // Post type settings from WPDB
            $cptSettings = $this->cptHelper->cptSettings();
            $enabled = [];
            $disabled = [];
            $used = [];
            $unused = [];
            // Extract post types from settings
            $reviewPostTypes = [];
            if (!empty($cptSettings['reviews'])) {
                foreach ($cptSettings['reviews'] as $review) {
                    $postType = \strtolower($review['post_type']);
                    $reviewPostTypes[$postType] = ['name' => $review['post_type'] ? \strtolower($review['post_type']) : $postType, 'slug' => $postType, 'status' => $review['status']];
                }
            }
            // Categorize post types
            foreach ($cptList as $cpt) {
                $slug = \strtolower($cpt['slug']);
                $name = \strtolower($cpt['name']);
                if (isset($reviewPostTypes[$slug])) {
                    $used[] = ['name' => $reviewPostTypes[$slug]['name'], 'slug' => $slug];
                    if ($reviewPostTypes[$slug]['status'] === 'Enabled') {
                        $enabled[] = ['name' => $reviewPostTypes[$slug]['name'], 'slug' => $slug];
                    } else {
                        $disabled[] = ['name' => $reviewPostTypes[$slug]['name'], 'slug' => $slug];
                    }
                } else {
                    $unused[] = ['name' => $name, 'slug' => $slug];
                }
            }
            // Ensure 'product' post type is included in all except 'unused'
            $productPostType = ['name' => 'product', 'slug' => 'product'];
            if (!\in_array('product', \array_column($enabled, 'slug'))) {
                $enabled[] = $productPostType;
            }
            if (!\in_array('product', \array_column($cptList, 'slug'))) {
                $cptList[] = $productPostType;
            }
            if (!\in_array('product', \array_column($used, 'slug'))) {
                $used[] = $productPostType;
            }
            // Get API param
            $apiParam = 'all';
            if (\is_string($request)) {
                // $apiParam is a string
                $apiParam = $request;
            } elseif (\is_object($request)) {
                // $apiParam is an array
                $apiParam = \strtolower($request->get_param('cpt'));
            }
            $responseData = [];
            switch ($apiParam) {
                case 'enabled':
                    $responseData = $enabled;
                    break;
                case 'disabled':
                    $responseData = $disabled;
                    break;
                case 'used':
                    $responseData = $used;
                    break;
                case 'unused':
                    $responseData = $unused;
                    break;
                case 'all':
                    $responseData = $cptList;
                    break;
                default:
                    $responseData = $cptList;
            }
            if (\is_string($request)) {
                return $responseData;
            } elseif (\is_object($request)) {
                return Helper::rvxApi($responseData)->success();
            }
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Custom Post Types List Fails', $e->getCode());
        }
    }
    public function customPostTypesOnSync($param)
    {
        try {
            // Available post types
            $cptList = \array_map(function ($cpt) {
                return ['name' => \strtolower($cpt['name']), 'slug' => \strtolower($cpt['slug'])];
            }, $this->cptHelper->getPublicCptList());
            // Post type settings from WPDB
            $cptSettings = $this->cptHelper->cptSettingsOnSync();
            $enabled = [];
            $disabled = [];
            $used = [];
            $unused = [];
            // Extract post types from settings
            $reviewPostTypes = [];
            if (!empty($cptSettings['reviews'])) {
                foreach ($cptSettings['reviews'] as $review) {
                    $postType = \strtolower($review['post_type']);
                    $reviewPostTypes[$postType] = ['name' => $review['post_type'] ? \strtolower($review['post_type']) : $postType, 'slug' => $postType, 'status' => $review['status']];
                }
            }
            // Categorize post types
            foreach ($cptList as $cpt) {
                $slug = \strtolower($cpt['slug']);
                $name = \strtolower($cpt['name']);
                if (isset($reviewPostTypes[$slug])) {
                    $used[] = ['name' => $reviewPostTypes[$slug]['name'], 'slug' => $slug];
                    if ($reviewPostTypes[$slug]['status'] === 'Enabled') {
                        $enabled[] = ['name' => $reviewPostTypes[$slug]['name'], 'slug' => $slug];
                    } else {
                        $disabled[] = ['name' => $reviewPostTypes[$slug]['name'], 'slug' => $slug];
                    }
                } else {
                    $unused[] = ['name' => $name, 'slug' => $slug];
                }
            }
            // Ensure 'product' post type is included in all except 'unused'
            $productPostType = ['name' => 'product', 'slug' => 'product'];
            if (!\in_array('product', \array_column($enabled, 'slug'))) {
                $enabled[] = $productPostType;
            }
            if (!\in_array('product', \array_column($cptList, 'slug'))) {
                $cptList[] = $productPostType;
            }
            if (!\in_array('product', \array_column($used, 'slug'))) {
                $used[] = $productPostType;
            }
            // Get param
            if (empty($param)) {
                // $param is a string
                $apiParam = 'all';
            } elseif (\is_string($param)) {
                // $apiParam is an array
                $apiParam = \strtolower($param);
            }
            $responseData = [];
            switch ($apiParam) {
                case 'enabled':
                    $responseData = $enabled;
                    break;
                case 'disabled':
                    $responseData = $disabled;
                    break;
                case 'used':
                    $responseData = $used;
                    break;
                case 'unused':
                    $responseData = $unused;
                    break;
                case 'all':
                    $responseData = $cptList;
                    break;
                default:
                    $responseData = $cptList;
            }
            return $responseData;
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Custom Post Types List Fails', $e->getCode());
        }
    }
    /**
     * @return Response
     */
    public function cptStatusChange($request)
    {
        try {
            $response = $this->cptService->cptStatusChange($request->get_params());
            $resData = Helper::saasResponse($response);
            if ($response->getStatusCode() === 200) {
                // Update (_rvx_cpt_settings) CPT Settings
                $response = $this->cptService->cptGet();
                $this->cptSettings($response);
                // Update (_rvx_ettings_{post_type}) CPT Settings
                $post_type = $resData->data['data']['post_type'] ? \strtolower($resData->data['data']['post_type']) : '';
                // Upload CPT data to Saas
                if ($resData->data['data']['status'] === 'Enabled') {
                    $this->dataSyncService->dataSync('default', $post_type);
                    $this->cacheServices->removeCache();
                }
            }
            return Helper::rvxApi($resData)->success('Status Change Success', 200);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Status Change Fails', $e->getCode());
        }
    }
    public function cptSettings($response)
    {
        $status = \false;
        $dataArray = \json_decode($response, \true);
        if ($dataArray !== null) {
            $status = update_option('_rvx_cpt_settings', $dataArray['data']);
        }
        return $status;
    }
}

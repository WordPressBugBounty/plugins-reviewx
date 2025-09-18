<?php

namespace Rvx\Rest\Controllers;

use Exception;
use Rvx\Api\AuthApi;
use Rvx\CPT\CptHelper;
use Rvx\Handlers\MigrationRollback\MigrationPrompt;
use Rvx\Handlers\MigrationRollback\ReviewXChecker;
use Rvx\Models\Site;
use Rvx\Services\Api\LoginService;
use Rvx\Services\DataSyncService;
use Rvx\Services\SettingService;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Throwable;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
use Rvx\Services\CacheServices;
class AuthController implements InvokableContract
{
    protected LoginService $loginService;
    protected DataSyncService $dataSyncService;
    protected CacheServices $cacheServices;
    public function __construct()
    {
        $this->loginService = new LoginService();
        $this->dataSyncService = new DataSyncService();
        $this->cacheServices = new CacheServices();
    }
    public function __invoke()
    {
    }
    /**
     * @param $request
     * @return Response
     */
    public function login($request)
    {
        $data = $request->get_params();
        $data['multicriteria'] = $this->multicriteriaOptions();
        $payload = \array_merge($data, Helper::getWpClientInfo());
        //return Helper::rvxApi(['error' => 'Invalid request', 'data' => $payload])->fails('Invalid request', Response::HTTP_BAD_REQUEST);
        try {
            $response = (new AuthApi())->login($payload);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return Helper::saasResponse($response);
            }
            $apiData = $response->getApiData();
            $validation = $this->validateSite($apiData);
            if ($validation !== \true) {
                return $validation;
                // return error response
            }
            $site_info = $this->prepareData($apiData);
            $site = Site::where('uid', $site_info['uid'])->first();
            if (!$site) {
                $site = Site::insert($site_info);
            } else {
                Site::where("id", $site->id)->update($site_info);
            }
            Client::set(Site::where('uid', $site_info['uid'])->first());
            $dataResponse = $this->dataSyncService->dataSync('login', 'product');
            if (!$dataResponse) {
                return Helper::rvxApi(['error' => 'Data sync fails'])->fails('Data sync fails', $dataResponse->getStatusCode());
            }
            // Sleep for 1 seconds
            \sleep(1);
            // Upload CPT data to Saas
            $enabled_post_types = (new CptHelper())->usedCPTOnSync('used');
            unset($enabled_post_types['product']);
            // Exclude 'product' post type
            // Loop through each post type and hook into the actions/filters dynamically
            foreach ($enabled_post_types as $post_type) {
                $this->dataSyncService->dataSync('default', $post_type);
            }
            $this->cacheServices->removeCache();
            $this->loginService->resetPostMeta();
            // Set the localStorage isAlreadySyncSuccess to false
            update_option('rvx_reset_sync_flag', \true);
            return Helper::saasResponse($response);
        } catch (Exception $e) {
            $errorCode = $e->getCode() === 0 ? Response::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $message = $e->getCode() === 0 ? 'Internal Server Error' : $e->getMessage();
            return Helper::rvxApi(['error' => $message])->fails($message, $errorCode);
        }
    }
    public function license_key($request)
    {
        $data = $request->get_params();
        $data['multicriteria'] = $this->multicriteriaOptions();
        $payload = \array_merge($data, Helper::getWpClientInfo());
        try {
            $response = (new AuthApi())->licenseLogin($payload);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return Helper::saasResponse($response);
            }
            $apiData = $response->getApiData();
            $validation = $this->validateSite($apiData);
            if ($validation !== \true) {
                return $validation;
                // return error response
            }
            $site_info = $this->prepareData($apiData);
            $site = Site::where('uid', $site_info['uid'])->first();
            if (!$site) {
                $site = Site::insert($site_info);
            } else {
                Site::where("id", $site->id)->update($site_info);
            }
            Client::set(Site::where('uid', $site_info['uid'])->first());
            $dataResponse = $this->dataSyncService->dataSync('login', 'product');
            if (!$dataResponse) {
                return Helper::rvxApi(['error' => 'Data sync fails'])->fails('Data sync fails', $dataResponse->getStatusCode());
            }
            // Sleep for 1 seconds
            \sleep(1);
            // Upload CPT data to Saas
            $enabled_post_types = (new CptHelper())->usedCPTOnSync('used');
            unset($enabled_post_types['product']);
            // Exclude 'product' post type
            // Loop through each post type and hook into the actions/filters dynamically
            foreach ($enabled_post_types as $post_type) {
                $this->dataSyncService->dataSync('default', $post_type);
            }
            $this->cacheServices->removeCache();
            $this->loginService->resetPostMeta();
            // Set the localStorage isAlreadySyncSuccess to false
            update_option('rvx_reset_sync_flag', \true);
            return Helper::saasResponse($response);
        } catch (Exception $e) {
            $errorCode = $e->getCode() === 0 ? Response::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $message = $e->getCode() === 0 ? 'Internal Server Error' : $e->getMessage();
            return Helper::rvxApi(['error' => $message])->fails($message, $errorCode);
        }
    }
    protected function prepareData(array $site) : array
    {
        return ['site_id' => $site['id'], 'uid' => $site['uid'], 'name' => $site['name'], 'domain' => $site['domain'], 'url' => $site['url'], 'locale' => $site['locale'], 'email' => $site['email'], 'secret' => $site['key'], 'is_saas_sync' => 0, 'created_at' => \wp_date('Y-m-d H:i:s'), 'updated_at' => \wp_date('Y-m-d H:i:s')];
    }
    public function multicriteriaOptions()
    {
        $data = [];
        $isOldReviewXExists = ReviewXChecker::isReviewXExists();
        $isReviewXSaaSExists = ReviewXChecker::isReviewXSaasExists();
        if ($isOldReviewXExists && !$isReviewXSaaSExists) {
            $options = get_option('_rx_option_review_criteria');
            $keys = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"];
            $criterias = [];
            $i = 0;
            foreach ($options as $key => $name) {
                if (isset($keys[$i])) {
                    $criterias[] = ["key" => $keys[$i], "name" => $name];
                }
                $i++;
            }
            $multicrtriaEnableorDisable = get_option('_rx_option_allow_multi_criteria');
            $data = ["enable" => $multicrtriaEnableorDisable == 1 ? \true : \false, "criterias" => $criterias];
        } elseif ($isReviewXSaaSExists) {
            $data = (array) (new SettingService())->getReviewSettings('product') ?? [];
            $data = $data['reviews']['multicriteria'];
        }
        return $data;
    }
    public function forgetPassword($request)
    {
        try {
            $response = $this->loginService->forgetPassword($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Forget password fail', $e->getCode());
        }
    }
    public function resetPassword($request)
    {
        try {
            $response = $this->loginService->resetPassword($request->get_params());
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('Reset password fail', $e->getCode());
        }
    }
    /**
     * @param $request
     * @return Response
     */
    public function register($request)
    {
        $data = $request->get_params();
        $payload = \array_merge($data, Helper::getWpClientInfo());
        try {
            $response = (new AuthApi())->register($payload);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return Helper::saasResponse($response);
            }
            $apiData = $response->getApiData();
            $validation = $this->validateSite($apiData);
            if ($validation !== \true) {
                return $validation;
                // return error response
            }
            $site_info = $this->prepareRegisterData($apiData['site']);
            $site = Site::where('uid', $site_info['uid'])->first();
            if (!$site) {
                $site = Site::insert($site_info);
            } else {
                Site::where("id", $site->id)->update($site_info);
            }
            Client::set(Site::where('uid', $site_info['uid'])->first());
            $dataResponse = $this->dataSyncService->dataSync('register', 'product');
            if (!$dataResponse) {
                return Helper::rvxApi(['error' => "Registration Fail"])->fails('Registration Fail', $dataResponse->getStatusCode());
            }
            $this->cacheServices->removeCache();
            $this->loginService->resetPostMeta();
            $this->removeUserSettingsFormLocal();
            // Set the localStorage isAlreadySyncSuccess to false
            update_option('rvx_reset_sync_flag', \true);
            return Helper::saasResponse($response);
        } catch (Exception $e) {
            $errorCode = $e->getCode() === 0 ? Response::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $message = $e->getCode() === 0 ? 'Internal Server Error' : $e->getMessage();
            return Helper::rvxApi(['error' => $message])->fails($message, $errorCode);
        }
    }
    public function migrationPrompt()
    {
        $migrationData = new MigrationPrompt();
        $result = \false;
        if (ReviewXChecker::isReviewXExists() && !ReviewXChecker::isReviewXSaasExists()) {
            $result = $migrationData->rvx_retrieve_old_plugin_options_data();
        } elseif (ReviewXChecker::isReviewXSaasExists()) {
            $result = $migrationData->rvx_retrieve_saas_plugin_options_data();
        }
        if ($result !== \false) {
            $successMessage = "Old Data found";
            return Helper::rvxApi($result)->success($successMessage, 200);
        } else {
            $failsMessage = "No Data found";
            return Helper::rvxApi(null)->success($failsMessage, 200);
        }
    }
    public function removeUserSettingsFormLocal()
    {
        global $wpdb;
        $option_name = '__user_setting_access';
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s", $option_name));
        if ($exists > 0) {
            $result = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name = %s", $option_name));
            if ($result !== \false) {
                return ["Success" => "Options Table Delete"];
            }
        }
    }
    /**
     * Validate API response for required site data.
     *
     * @param array $apiData
     * @return bool
     */
    private function validateSite(array $apiData) : bool
    {
        if (!\is_array($apiData) && !isset($apiData['id']) && !isset($apiData['uid'])) {
            return \false;
        }
        return \true;
    }
    protected function prepareRegisterData(array $site) : array
    {
        return ['site_id' => $site['id'], 'uid' => $site['uid'], 'name' => $site['name'], 'domain' => $site['domain'], 'url' => $site['url'], 'locale' => $site['locale'], 'email' => $site['email'], 'secret' => $site['key'], 'is_saas_sync' => 0, 'created_at' => \wp_date('Y-m-d H:i:s'), 'updated_at' => \wp_date('Y-m-d H:i:s')];
    }
}

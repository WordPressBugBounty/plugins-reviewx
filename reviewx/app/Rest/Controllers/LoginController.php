<?php

namespace Rvx\Rest\Controllers;

use Exception;
use Throwable;
use Rvx\Api\AuthApi;
use Rvx\Models\Site;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Auth\Client;
use Rvx\Services\DataSyncService;
use Rvx\Services\Api\LoginService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\Handlers\MigrationRollback\ReviewXChecker;
use Rvx\Services\SettingService;
class LoginController implements InvokableContract
{
    protected LoginService $loginService;
    public function __construct()
    {
        $this->loginService = new LoginService();
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
        if (ReviewXChecker::isReviewXExists() || ReviewXChecker::isReviewXSaasExists()) {
            $data['multicriteria'] = $this->existingPayload();
        }
        $payload = \array_merge($data, $this->getLoginDataApi());
        try {
            $response = (new AuthApi())->login($payload);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return Helper::saasResponse($response);
            }
            $site_info = $this->prepareData($response->getApiData());
            $site = Site::where('uid', $site_info['uid'])->first();
            if (!$site) {
                $site = Site::insert($site_info);
            } else {
                Site::where("id", $site->id)->update($site_info);
            }
            Client::set(Site::where('uid', $site_info['uid'])->first());
            $dataResponse = (new DataSyncService())->dataSync('login');
            if (!$dataResponse) {
                return Helper::rvxApi(['error' => 'Data sync fails'])->fails('Data sync fails', $dataResponse->getStatusCode());
            }
            $this->loginService->resetPostMeta();
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
        if (ReviewXChecker::isReviewXExists() || ReviewXChecker::isReviewXSaasExists()) {
            $data['multicriteria'] = $this->existingPayload();
        }
        $payload = \array_merge($data, $this->getLoginDataApi());
        try {
            $response = (new AuthApi())->licenseLogin($payload);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return Helper::saasResponse($response);
            }
            $site_info = $this->prepareData($response->getApiData());
            $site = Site::where('uid', $site_info['uid'])->first();
            if (!$site) {
                $site = Site::insert($site_info);
            } else {
                Site::where("id", $site->id)->update($site_info);
            }
            Client::set(Site::where('uid', $site_info['uid'])->first());
            $dataResponse = (new DataSyncService())->dataSync('login');
            if (!$dataResponse) {
                return Helper::rvxApi(['error' => 'Data sync fails'])->fails('Data sync fails', $dataResponse->getStatusCode());
            }
            $this->loginService->resetPostMeta();
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
    public function getLoginDataApi() : array
    {
        $current_user = wp_get_current_user();
        $first_name = $current_user->first_name ?: $current_user->user_login;
        $last_name = $current_user->last_name ?: '';
        return ['domain' => Helper::getWpDomainNameOnly(), 'url' => home_url(), 'site_locale' => get_locale(), 'first_name' => sanitize_text_field($first_name), 'last_name' => sanitize_text_field($last_name)];
    }
    public function existingPayload()
    {
        $data = [];
        if (ReviewXChecker::isReviewXExists() && !ReviewXChecker::isReviewXSaasExists()) {
            $data = get_option('_rx_option_review_criteria');
            $keys = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"];
            $criterias = [];
            $i = 0;
            foreach ($data as $key => $name) {
                if (isset($keys[$i])) {
                    $criterias[] = ["key" => $keys[$i], "name" => $name];
                }
                $i++;
            }
            $multicrtriaEnableorDisable = get_option('_rx_option_allow_multi_criteria');
            $data = ["enable" => $multicrtriaEnableorDisable == 1 ? \true : \false, "criterias" => $criterias];
        } elseif (ReviewXChecker::isReviewXSaasExists()) {
            $data = (array) (new SettingService())->getReviewSettings() ?? [];
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
}

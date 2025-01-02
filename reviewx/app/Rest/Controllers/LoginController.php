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
class LoginController implements InvokableContract
{
    protected LoginService $loginService;
    /**
     *
     */
    public function __construct()
    {
        $this->loginService = new LoginService();
    }
    /**
     * @return void
     */
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
            if ($dataResponse->getStatusCode() !== Response::HTTP_OK) {
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
            if ($dataResponse->getStatusCode() !== Response::HTTP_OK) {
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
    // public function getRegisterDataApi(): array
    // {
    //     $user = get_option('rvx_stored_user_info');
    //     return [
    //         'domain' => Helper::getWpDomainNameOnly(),
    //         'url' => site_url(),
    //         'site_locale' => get_locale(),
    //         'first_name' => $user['first_name'],
    //         'last_name' => $user['last_name'],
    //     ];
    // }
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
            $multicrtriaEnableorDisale = get_option('_rx_option_allow_multi_criteria');
            $data = ["enable" => $multicrtriaEnableorDisale == 1 ? \true : \false, "criterias" => $criterias];
        } elseif (ReviewXChecker::isReviewXSaasExists()) {
            $data = get_option('_rvx_settings_data');
            $data = $data['setting']['review_settings']['reviews']['multicriteria'];
        }
        return $data;
    }
    public function option_exists($option_name)
    {
        $option_value = get_option($option_name);
        return $option_value !== \false;
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

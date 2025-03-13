<?php

namespace Rvx\Rest\Controllers;

use Exception;
use Rvx\Api\AuthApi;
use Rvx\Models\Site;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Auth\Client;
use Rvx\Services\DataSyncService;
use Rvx\Services\Api\LoginService;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\DB\QueryBuilder\QueryBuilderHandler;
use Rvx\Handlers\MigrationRollback\MigrationPrompt;
use Rvx\Handlers\MigrationRollback\ReviewXChecker;
class RegisterController implements InvokableContract
{
    protected LoginService $loginService;
    /**
     * @param QueryBuilderHandler $db
     */
    public function __construct(QueryBuilderHandler $db)
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
    public function register($request)
    {
        $data = $request->get_params();
        $payload = \array_merge($data, $this->getRegisterDataApi());
        try {
            $response = (new AuthApi())->register($payload);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return Helper::saasResponse($response);
            }
            $site_info = $this->prepareData($response->getApiData()['site']);
            $site = Site::where('uid', $site_info['uid'])->first();
            if (!$site) {
                $site = Site::insert($site_info);
            } else {
                Site::where("id", $site->id)->update($site_info);
            }
            Client::set(Site::where('uid', $site_info['uid'])->first());
            $dataResponse = (new DataSyncService())->dataSync('register');
            if (!$dataResponse) {
                return Helper::rvxApi(['error' => "Registration Fail"])->fails('Registration Fail', $dataResponse->getStatusCode());
            }
            $this->loginService->resetPostMeta();
            $this->removeUserSettingsFormLocal();
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
    public function getRegisterDataApi() : array
    {
        $current_user = wp_get_current_user();
        $first_name = $current_user->first_name ?: $current_user->user_login;
        $last_name = $current_user->last_name ?: '';
        return ['domain' => Helper::getWpDomainNameOnly(), 'url' => home_url(), 'site_locale' => get_locale(), 'first_name' => sanitize_text_field($first_name), 'last_name' => sanitize_text_field($last_name)];
    }
    protected function prepareData(array $site) : array
    {
        return ['site_id' => $site['id'], 'uid' => $site['uid'], 'name' => $site['name'], 'domain' => $site['domain'], 'url' => $site['url'], 'locale' => $site['locale'], 'email' => $site['email'], 'secret' => $site['key'], 'is_saas_sync' => 0, 'created_at' => \wp_date('Y-m-d H:i:s'), 'updated_at' => \wp_date('Y-m-d H:i:s')];
    }
}

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
        /*
        if ($this->option_exists('_rx_option_allow_multi_criteria')) {
            $data['multicriteria'] = $this->existingPayload();
        }
        */
        //$modifyData = $this->otherSettingsForReviewOne($data);
        $payload = \array_merge($data, $this->getRegisterDataApi());
        try {
            $response = (new AuthApi())->register($payload);
            \error_log("response " . \print_r($response, \true));
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
            if ($dataResponse->getStatusCode() !== Response::HTTP_OK) {
                return Helper::rvxApi(['error' => "Registration Fail"])->fails('Registration Fail', $dataResponse->getStatusCode());
            }
            $this->loginService->resetPostMeta();
            $this->removeUserSettingsFormLocal();
            return Helper::saasResponse($response);
            //            return rvx_saas_response($response);
        } catch (Exception $e) {
            $errorCode = $e->getCode() === 0 ? Response::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $message = $e->getCode() === 0 ? 'Internal Server Error' : $e->getMessage();
            return Helper::rvxApi(['error' => $message])->fails($message, $errorCode);
        }
    }
    public function otherSettingsForReviewOne($data)
    {
        //        if ($this->option_exists('_rx_option_allow_like_dislike')) {
        //            $data['enable_likes_dislikes']['enabled']  = get_option('_rx_option_allow_like_dislike') == 1;
        //            $data['enable_likes_dislikes']['options']['allow_dislikes']  = get_option('_rx_option_allow_like_dislike') == 1;
        //        }
        //        if ($this->option_exists('_rx_option_color_theme')) {
        //            $data['brand_color_code']  = get_option('_rx_option_color_theme');
        //        }
        if ($this->option_exists('_rx_option_color_theme')) {
            $data['brand_color_code'] = get_option('_rx_option_color_theme');
        }
        if ($this->option_exists('_rx_option_allow_img')) {
            $data['photo_reviews_allowed'] = get_option('_rx_option_allow_img') == 1;
        }
        //        if ($this->option_exists('_rx_option_allow_share_review')) {
        //            $data['allow_review_sharing']  = get_option('_rx_option_allow_share_review') == 1;
        //        }
        //        if ($this->option_exists('_rx_option_disable_auto_approval')) {
        //            $data['auto_approve_reviews']  = get_option('_rx_option_disable_auto_approval') == 1;
        //        }
        //        if ($this->option_exists('_rx_option_allow_review_title')) {
        //            $data['allow_review_titles']  = get_option('_rx_option_allow_review_title') == 1;
        //        }
        //        if ($this->option_exists('_rx_option_allow_reviewer_name_censor')) {
        //            $data['censor_reviewer_name']  = get_option('_rx_option_allow_reviewer_name_censor') == 1;
        //        }
        //        if ($this->option_exists('_rx_option_disable_richschema')) {
        //            $data['product_schema']  = get_option('_rx_option_disable_richschema') == 1;
        //        }
        //        if ($this->option_exists('_rx_option_allow_img')) {
        //            $data['photo_reviews_allowed']  = get_option('_rx_option_allow_img') == 1;
        //        }
        //        if ($this->option_exists('_rx_option_allow_video')) {
        //            $data['video_reviews_allowed']  = get_option('_rx_option_allow_video') == 1;
        //        }
        //        if ($this->option_exists('_rx_option_allow_multiple_review')) {
        //            $data['allow_multiple_reviews']  = get_option('_rx_option_allow_multiple_review') == 1;
        //        }
        //        if ($this->option_exists('_rx_option_allow_anonymouse')) {
        //            $data['anonymous_reviews_allowed']  = get_option('_rx_option_allow_anonymouse') == 1;
        //        }
        //        if ($this->option_exists('_rx_option_color_theme')) {
        //            $data['anonymous_reviews_allowed']  = get_option('_rx_option_color_theme');
        //        }
        return $data;
    }
    public function migrationPrompt()
    {
        $migrationData = new MigrationPrompt();
        $result = $migrationData->rvx_retrieve_old_plugin_options_data();
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
    public function existingPayload()
    {
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
        $newCriteria = ["enable" => $multicrtriaEnableorDisale == 1 ? \true : \false, "criterias" => $criterias];
        return $newCriteria;
    }
    public function option_exists($option_name)
    {
        $option_value = get_option($option_name);
        return $option_value !== \false;
    }
    /**
     * @param $request
     * @return array
     */
    public function getRegisterDataApi() : array
    {
        return ['domain' => Helper::getWpDomainNameOnly(), 'url' => site_url(), 'site_locale' => get_locale()];
    }
    protected function prepareData(array $site) : array
    {
        return ['site_id' => $site['id'], 'uid' => $site['uid'], 'name' => $site['name'], 'domain' => $site['domain'], 'url' => $site['url'], 'locale' => $site['locale'], 'email' => $site['email'], 'secret' => $site['key'], 'is_saas_sync' => 0, 'created_at' => \date('Y-m-d H:i:s', \time()), 'updated_at' => \date('Y-m-d H:i:s', \time())];
    }
}

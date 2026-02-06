<?php

namespace Rvx\Api;

use Rvx\Apiz\AbstractApi;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
class BaseApi extends AbstractApi
{
    protected $response = \Rvx\Api\ApiResponse::class;
    protected array $config = ['http_errors' => \false];
    /**
     * @return string
     */
    public function getBaseUrl() : string
    {
        if (Helper::plugin()->isProduction()) {
            return "https://api.reviewx.io";
        }
        return "https://api-dev.reviewx.io";
    }
    public function getIp() : string
    {
        return '192.168.68.119:10013';
    }
    /**
     * @return string
     */
    public function getPrefix() : string
    {
        return '/admin/api/v1';
    }
    /**
     * @return array
     */
    public function getDefaultHeaders() : array
    {
        return ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . Helper::getAuthToken(), 'X-url' => home_url(), 'X-Domain' => Helper::getWpDomainNameOnly(), 'X-Theme' => wp_get_theme()->get('Name'), 'X-Site-Locale' => get_locale(), 'X-Request-Id' => \sha1(\time() . Client::getUid()), 'X-Wp-Version' => get_bloginfo("version"), 'X-Reviewx-Version' => RVX_VERSION, 'X-Environment' => Helper::plugin()->isProduction() ? 'production' : 'development'];
    }
}

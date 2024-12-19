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
        return "https://api.reviewx.io";
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
        return ['Authorization' => 'Bearer ' . Helper::getAuthToken(), 'X-Version' => Helper::getWpVersion(), 'Accept' => 'application/json', 'X-Domain' => Helper::getWpDomainNameOnly(), 'X-Theme' => Helper::getActiveTheme(), 'X-Url' => site_url(), 'X-Site-Locale' => get_locale(), 'X-Request-Id' => \sha1(\time() . Client::getUid()), 'X-Reviewx-Version' => RVX_VERSION];
    }
}

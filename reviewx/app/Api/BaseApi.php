<?php

namespace Rvx\Api;

use Rvx\Apiz\AbstractApi;
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
        // For Local
        return "https://api.reviewx.io";
        //          return "https://extending-showtimes-respected-shoulder.trycloudflare.com";
        //  For Staging server
        //        return "http://13.214.84.12";
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
        return ['Authorization' => 'Bearer ' . Helper::getAuthToken(), 'X-version' => Helper::getWpVersion(), 'Accept' => 'application/json', 'X-domain' => Helper::getWpDomainNameOnly(), 'X-theme' => Helper::getActiveTheme(), 'X-url' => site_url(), 'X-site-locale' => get_locale()];
    }
}

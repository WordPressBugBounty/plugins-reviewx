<?php

namespace Rvx\Api;

use Rvx\GuzzleHttp\Client;
use Rvx\GuzzleHttp\Exception\RequestException;
use Rvx\Utilities\Helper;
class WpApi
{
    protected $baseUrl = '/wp-json/reviewx/api/v1';
    protected $token = null;
    protected $client;
    public function __construct($token = null)
    {
        $this->baseUrl = site_url() . $this->baseUrl;
        $this->token = Helper::getAuthToken();
        $this->client = new Client(['base_uri' => $this->baseUrl, 'timeout' => 10.0]);
    }
    /**
     * Make a GET request.
     *
     * @param string $route
     * @return mixed
     */
    public function get(string $route)
    {
        $url = $this->prepareRoute($route);
        $headers = $this->prepareHeaders();
        try {
            // Make the GET request
            $response = $this->client->request('GET', $url, ['headers' => $headers]);
            return \json_decode($response->getBody(), \true);
        } catch (RequestException $e) {
            // Handle exception or log the error
            return $e->getMessage();
        }
    }
    /**
     * Make a POST request.
     *
     * @param string $route
     * @param array $payload
     * @return mixed
     */
    public function post(string $route, array $payload)
    {
        $url = $this->prepareRoute($route);
        $headers = $this->prepareHeaders();
        try {
            // Make the POST request
            $response = $this->client->request('POST', $url, ['headers' => $headers, 'json' => $payload]);
            return \json_decode($response->getBody(), \true);
        } catch (RequestException $e) {
            // Handle exception or log the error
            return $e->getMessage();
        }
    }
    /**
     * Prepare the full route URL.
     *
     * @param string $route
     * @return string
     */
    public function prepareRoute(string $route) : string
    {
        return $this->baseUrl . '/' . \ltrim($route, '/');
    }
    /**
     * Prepare the authorization headers.
     *
     * @return array
     */
    protected function prepareHeaders() : array
    {
        $headers = ['Content-Type' => 'application/json'];
        if ($this->token) {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }
        return $headers;
    }
    /**
     * Set the authorization token for the API.
     *
     * @param string $token
     * @return void
     */
    public function setToken(string $token) : void
    {
        $this->token = $token;
    }
    /**
     * Set the base URL for the API.
     *
     * @param string $baseUrl
     * @return void
     */
    public function setBaseUrl(string $baseUrl) : void
    {
        $this->baseUrl = \rtrim($baseUrl, '/');
    }
}

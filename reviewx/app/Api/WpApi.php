<?php

namespace Rvx\Api;

use Rvx\GuzzleHttp\Client;
use Rvx\GuzzleHttp\Exception\RequestException;
use Rvx\Utilities\Helper;
class WpApi
{
    protected string $baseUrl;
    protected ?string $token = null;
    protected Client $client;
    public function __construct($token = null)
    {
        // Use get_rest_url instead of hardcoding wp-json
        $this->baseUrl = Helper::getRestAPIurl() . '/api/v1';
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
            $response = $this->client->request('GET', $url, ['headers' => $headers]);
            return \json_decode((string) $response->getBody(), \true);
        } catch (RequestException $e) {
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
            $response = $this->client->request('POST', $url, ['headers' => $headers, 'json' => $payload]);
            return \json_decode((string) $response->getBody(), \true);
        } catch (RequestException $e) {
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
        return \rtrim($this->baseUrl, '/') . '/' . \ltrim($route, '/');
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
            $headers['X-url'] = home_url();
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

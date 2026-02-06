<?php

namespace Rvx\Rest\Middleware;

use Exception;
use WP_REST_Request;
use Rvx\Utilities\Auth\WpUser;
use Rvx\Utilities\Auth\Client;
use Rvx\Firebase\JWT\JWT;
use Rvx\Firebase\JWT\Key;
class AuthMiddleware
{
    /**
     * Determine if the current request is authorized.
     *
     * Authorization is granted if:
     * User is logged in AND has sufficient capabilities.
     * Or if user has a valid JWT Bearer token
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public function handle(WP_REST_Request $request) : bool
    {
        // Validate by WP User
        if (WpUser::isLoggedIn() && WpUser::can()) {
            return \true;
        }
        // Validate by Bearer Token
        $clientUid = Client::getUid();
        $secret = Client::getSecret();
        if (empty($clientUid) || empty($secret)) {
            return \false;
        }
        $headers = $request->get_headers();
        $authHeader = isset($headers['authorization'][0]) ? \trim($headers['authorization'][0]) : '';
        if (!$authHeader || !\preg_match('/Bearer\\s+(.*)$/i', $authHeader, $matches)) {
            return \false;
        }
        $token = $matches[1];
        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            // Check UID binding
            if (empty($decoded->uid) || $decoded->uid !== $clientUid) {
                return \false;
            }
            return \true;
        } catch (Exception $e) {
            return \false;
        }
    }
}

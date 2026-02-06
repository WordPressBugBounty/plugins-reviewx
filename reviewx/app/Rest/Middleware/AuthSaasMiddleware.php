<?php

namespace Rvx\Rest\Middleware;

use Exception;
use WP_REST_Request;
use Rvx\Utilities\Auth\Client;
use Rvx\Firebase\JWT\JWT;
use Rvx\Firebase\JWT\Key;
class AuthSaasMiddleware
{
    /**
     * Handle incoming SaaS-authenticated requests
     */
    public function handle(WP_REST_Request $request) : bool
    {
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

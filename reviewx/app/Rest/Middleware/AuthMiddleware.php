<?php

namespace Rvx\Rest\Middleware;

use Rvx\Firebase\JWT\JWT;
use Rvx\Firebase\JWT\Key;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
class AuthMiddleware
{
    /**
     * @return bool
     */
    public function handle(\WP_REST_Request $request) : bool
    {
        $userCap = $this->userAccessibility();
        $role = ['administrator', 'editor', 'shop_manager'];
        if (Client::getUid() && $userCap && !empty(\array_intersect($userCap, $role))) {
            return \true;
        }
        //if auth user cookie is not present, it will try to authenticate using JWT token
        $bearer = $request->get_header('Authorization');
        if (!$bearer) {
            return \false;
        }
        $token = \explode(' ', $bearer);
        if (\count($token) !== 2) {
            return \false;
        }
        try {
            $token = $token[1];
            $response = JWT::decode($token, new Key(Client::getSecret(), 'HS256'));
            if ($response->uid === Client::getUid()) {
                return \true;
            }
        } catch (\Throwable $e) {
            return \false;
        }
        return \false;
    }
    public function userAccessibility()
    {
        global $wpdb;
        if (empty($_COOKIE[LOGGED_IN_COOKIE])) {
            return \false;
        }
        $cookie_parts = \explode('|', $_COOKIE[LOGGED_IN_COOKIE]);
        if (\count($cookie_parts) < 2) {
            return \false;
        }
        $username = sanitize_text_field($cookie_parts[0]);
        $user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->users} WHERE user_login = %s", $username));
        $meta_key = $wpdb->prefix . 'capabilities';
        $user_roles = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s", $user_id, $meta_key));
        if (!$user_roles) {
            return \false;
        }
        $user_roles = maybe_unserialize($user_roles);
        if (\is_array($user_roles)) {
            return \array_keys($user_roles);
        }
        return \false;
    }
}

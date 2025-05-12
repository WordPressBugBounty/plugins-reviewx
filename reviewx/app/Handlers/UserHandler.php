<?php

namespace Rvx\Handlers;

use Rvx\Api\UserApi;
use Rvx\WPDrill\Response;
class UserHandler
{
    public function __construct()
    {
    }
    public function __invoke($user_id)
    {
        $user = get_userdata($user_id);
        $data = [
            'wp_id' => $user->ID,
            'name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->user_email,
            // 'display_name' => $user->display_name,
            // 'role' => $user->roles,
            'avatar' => get_avatar_url($user->ID),
            'city' => get_user_meta($user->ID, 'billing_city', \true) ?? '',
            'phone' => get_user_meta($user->ID, 'billing_phone', \true) ?? '',
            'address' => get_user_meta($user->ID, 'billing_address_1', \true) ?? '',
            'country' => get_user_meta($user->ID, 'billing_country', \true) ?? '',
            'status' => 1,
        ];
        $response = (new UserApi())->create($data);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return \false;
        }
    }
}

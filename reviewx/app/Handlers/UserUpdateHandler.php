<?php

namespace Rvx\Handlers;

use Rvx\Api\UserApi;
use Rvx\Utilities\Auth\Client;
use Rvx\WPDrill\Response;
class UserUpdateHandler
{
    public function __construct()
    {
    }
    public function __invoke($user_id)
    {
        global $pagenow, $current_screen;
        if (is_admin() && isset($current_screen->id) && $current_screen->id === 'profile') {
            $user = get_userdata($user_id);
            $attachment_id = get_user_meta($user_id, 'image', \true);
            $original_image_url = $attachment_id ? wp_get_attachment_url($attachment_id) : get_avatar_url($user_id);
            $data = ['name' => $user->first_name . ' ' . $user->last_name, 'email' => $user->user_email, 'phone' => get_user_meta($user->ID, 'billing_phone', \true) ?? '', 'address' => get_user_meta($user->ID, 'billing_address_1', \true) ?? '', 'city' => get_user_meta($user->ID, 'billing_city', \true) ?? '', 'country' => get_user_meta($user->ID, 'billing_country', \true) ?? '', 'avatar' => get_avatar_url($user->ID), 'status' => 1];
            $id = Client::getUid() . '-' . $user_id;
            \error_log($id . \print_r($data, \true));
            $response = (new UserApi())->update($data, $id);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return \false;
            }
        }
    }
}

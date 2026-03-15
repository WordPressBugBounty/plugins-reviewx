<?php

namespace ReviewX\Handlers\WcTemplates;

\defined('ABSPATH') || exit;
use ReviewX\Api\UserApi;
use ReviewX\Utilities\Auth\Client;
class WcAccountDetails
{
    public function __invoke($user_id)
    {
        if (!isset($_POST['save_account_details']) || !wp_verify_nonce(\sanitize_text_field(\wp_unslash($_POST['save_account_details'])), 'save_account_details')) {
            return;
        }
        if (isset($_FILES["image"]) && isset($_FILES["image"]["error"]) && $_FILES["image"]["error"] == \UPLOAD_ERR_OK) {
            require_once \ABSPATH . "wp-admin/includes/image.php";
            $attachment_id = media_handle_upload("image", 0);
            if (!\is_wp_error($attachment_id)) {
                \update_user_meta($user_id, "rvx_image", $attachment_id);
                $this->userDataForm();
            }
        }
    }
    function userDataForm()
    {
        $user_id = \get_current_user_id();
        $user_info = \get_userdata($user_id);
        $attachment_id = \get_user_meta($user_id, "rvx_image", \true);
        $original_image_url = $attachment_id ? \wp_get_attachment_url($attachment_id) : get_avatar_url($user_id);
        $data = ["name" => $user_info->first_name . " " . $user_info->last_name, "email" => $user_info->user_email, "avatar" => $original_image_url, "city" => \get_user_meta($user_id, "billing_city", \true) ?? "", "phone" => \get_user_meta($user_id, "billing_phone", \true) ?? "", "address" => \get_user_meta($user_id, "billing_address_1", \true) ?? "", "country" => \get_user_meta($user_id, "billing_country", \true) ?? "", "status" => 1];
        $uid = Client::getUid() . "-" . $user_id;
        $response = (new UserApi())->editAccountUpdate($data, $uid);
    }
}

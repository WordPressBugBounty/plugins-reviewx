<?php

namespace Rvx\Services;

use Rvx\Apiz\Http\Response;
use Rvx\Utilities\Helper;
class UserServices extends \Rvx\Services\Service
{
    /**
     * @return Response
     */
    public function getUser()
    {
        $customer_ids = get_users(array('role' => 'customer', 'fields' => 'ID'));
        $customer_data_array = array();
        foreach ($customer_ids as $customer_id) {
            $user_data = get_userdata($customer_id);
            $customer_first_name = get_user_meta($customer_id, 'first_name', \true);
            $customer_last_name = get_user_meta($customer_id, 'last_name', \true);
            $current_customer_data = array('customer_id' => $customer_id, 'customer_email' => $user_data->user_email, 'customer_username' => $user_data->user_login, 'customer_first_name' => $customer_first_name, 'customer_last_name' => $customer_last_name);
            $customer_data_array[] = $current_customer_data;
        }
        return Helper::rest($customer_data_array)->success("All Customer list");
    }
}

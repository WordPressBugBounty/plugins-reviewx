<?php

namespace Rvx\Services;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
class UserSyncService extends \Rvx\Services\Service
{
    protected $users;
    protected $user_cunt = 0;
    protected $userMetaCityRelation;
    protected $userMetaAddressRelation;
    protected $userMetaCountryRelation;
    protected $userMetaPhoneRelation;
    public function syncUser($file)
    {
        $userCount = 0;
        $this->syncUserMeta($file);
        DB::table('users')->chunk(100, function ($allUsers) use($file, &$userCount) {
            foreach ($allUsers as $user) {
                $formatedUser = $this->formatUserData($user);
                Helper::appendToJsonl($file, $formatedUser);
                $userCount++;
            }
        });
        return $userCount;
    }
    public function syncUserMeta($file) : void
    {
        DB::table('usermeta')->chunk(100, function ($allUserMeta) {
            $result = [];
            foreach ($allUserMeta as $userMeta) {
                if ($userMeta->meta_key === 'billing_country') {
                    $this->userMetaCountryRelation[$userMeta->user_id] = $userMeta->meta_value;
                }
                if ($userMeta->meta_key === 'billing_address_1') {
                    $this->userMetaAddressRelation[$userMeta->user_id] = $userMeta->meta_value;
                }
                if ($userMeta->meta_key === 'billing_city') {
                    $this->userMetaCityRelation[$userMeta->user_id] = $userMeta->meta_value;
                }
                if ($userMeta->meta_key === 'billing_phone') {
                    $this->userMetaPhoneRelation[$userMeta->user_id] = $userMeta->meta_value;
                }
                //                $this->userMetaFormate($userMeta, $result);
            }
        });
    }
    //    public function userMetaFormate($userMeta, $result){
    //        $result[$userMeta->user_id] = ['user_id' => $userMeta->user_id];
    //        $result[$userMeta->user_id][$userMeta->meta_key] = $userMeta->meta_value;
    //        return $result;
    //    }
    public function formatUserData($user)
    {
        return [
            'rid' => 'rid://Customer/' . $user->ID,
            'wp_id' => (int) $user->ID,
            'name' => $user->display_name,
            'email' => $user->user_email,
            //            'display_name' => $user->display_name,
            // 'role' => $user->roles,
            'avatar' => get_avatar_url($user->ID),
            'city' => $this->userMetaCityRelation[$user->ID] ?? null,
            'phone' => $this->userMetaPhoneRelation[$user->ID] ?? null,
            'address' => $this->userMetaAddressRelation[$user->ID] ?? null,
            'country' => $this->userMetaCountryRelation[$user->ID] ?? null,
            'status' => (int) $user->user_status,
        ];
    }
}

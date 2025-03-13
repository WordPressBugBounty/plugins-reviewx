<?php

namespace Rvx\Services;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
class UserSyncService extends \Rvx\Services\Service
{
    protected $users;
    public function syncUser($file)
    {
        $userCount = 0;
        DB::table('users')->select(['ID', 'display_name', 'user_email', 'user_status'])->chunk(100, function ($allUsers) use($file, &$userCount) {
            foreach ($allUsers as $user) {
                $formatedUser = $this->formatUserData($user);
                Helper::appendToJsonl($file, $formatedUser);
                $userCount++;
            }
        });
        Helper::rvxLog($userCount, "User Done");
        return $userCount;
    }
    public function formatUserData($user) : array
    {
        return ['rid' => 'rid://Customer/' . (int) $user->ID, 'wp_id' => (int) $user->ID, 'name' => $user->display_name ?? null, 'email' => is_email($user->user_email) ? $user->user_email : '', 'avatar' => null, 'city' => null, 'phone' => null, 'address' => null, 'country' => null, 'status' => (int) $user->user_status];
    }
}

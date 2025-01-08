<?php

namespace Rvx\Handlers\Notice;

use Rvx\WPDrill\Facades\View;
class ReviewxAdminNoticeHandler
{
    public function adminNoticeHandler()
    {
        // Check if notice is temporarily dismissed
        $dismissUntil = get_option('rvx_admin_deal_notice_until', 0);
        if ($dismissUntil && \time() < $dismissUntil) {
            return;
        }
        // Generate nonce for AJAX
        $nonce = wp_create_nonce('rvx_dismiss_notice_nonce');
        // Render the notice
        View::output('storeadmin/notice/notice', ['nonce' => $nonce]);
    }
    public function rvx_admin_deal_notice_until()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rvx_dismiss_notice_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
        }
        // Get duration
        $duration = $_POST['duration'];
        if (\is_numeric($duration)) {
            $timestamp = \strtotime("+{$duration} days");
            update_option('rvx_admin_deal_notice_until', $timestamp);
        } else {
            wp_send_json_error(['message' => 'Invalid duration.']);
        }
        // Return success
        wp_send_json_success(['message' => 'Notice dismissed successfully.']);
    }
}

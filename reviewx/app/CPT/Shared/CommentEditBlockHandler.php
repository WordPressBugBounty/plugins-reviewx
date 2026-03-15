<?php

namespace ReviewX\CPT\Shared;

\defined('ABSPATH') || exit;
use ReviewX\CPT\CptHelper;
/**
 * Blocks editing of comments/reviews for post types that are managed by ReviewX.
 * Uses wp_die() to prevent the edit form from loading entirely — the WordPress-standard approach.
 */
class CommentEditBlockHandler
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    /**
     * Check if we should block comment editing.
     *
     * @return bool
     */
    public function shouldBlockEdit() : bool
    {
        $enabled_post_types = $this->cptHelper->enabledCPT();
        if (empty($enabled_post_types)) {
            return \false;
        }
        return \true;
    }
    /**
     * Check if we're on the comment edit page.
     *
     * @return bool
     */
    public function isCommentEditPage() : bool
    {
        global $pagenow;
        // WP Comment Edit page: comment.php?action=editcomment&c={id}
        if ($pagenow === 'comment.php') {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading $_GET for page detection, not processing form data
            $action = isset($_GET['action']) ? \sanitize_text_field(\wp_unslash($_GET['action'])) : '';
            return $action === 'editcomment';
        }
        return \false;
    }
    /**
     * Get the comment being edited.
     *
     * @return \WP_Comment|null
     */
    public function getEditingComment() : ?\WP_Comment
    {
        if (!$this->isCommentEditPage()) {
            return null;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading $_GET for page detection, not processing form data
        $comment_id = isset($_GET['c']) ? \absint($_GET['c']) : 0;
        if (!$comment_id) {
            return null;
        }
        return \get_comment($comment_id);
    }
    /**
     * Check if the comment belongs to a ReviewX-enabled post type.
     *
     * @param \WP_Comment $comment The comment object.
     * @return bool
     */
    public function isReviewxEnabledComment($comment) : bool
    {
        if (!$comment || !$comment->comment_post_ID) {
            return \false;
        }
        $post_type = \get_post_type($comment->comment_post_ID);
        $enabled_post_types = $this->cptHelper->enabledCPT();
        return isset($enabled_post_types[$post_type]);
    }
    /**
     * Block the comment edit page for ReviewX-managed reviews using wp_die().
     *
     * Hooked on 'admin_init' so it runs before any page output,
     * preventing the edit form from being rendered at all.
     */
    public function blockCommentEditPage() : void
    {
        if (!$this->shouldBlockEdit()) {
            return;
        }
        if (!$this->isCommentEditPage()) {
            return;
        }
        $comment = $this->getEditingComment();
        if (!$comment || !$this->isReviewxEnabledComment($comment)) {
            return;
        }
        $reviewx_reviews_url = \admin_url('admin.php?page=reviewx_reviews');
        $comments_url = \admin_url('edit-comments.php');
        $post_type = \get_post_type($comment->comment_post_ID);
        $post_type_label = \ucfirst($post_type);
        /* translators: %s: post type label (e.g. "Product") */
        $managed_text = \sprintf(\esc_html__('This %s review is managed by ReviewX. To edit this review, please use the ReviewX Reviews page.', 'reviewx'), \esc_html($post_type_label));
        $message = '<div style="text-align:center;max-width:500px;margin:40px auto;">' . '<span class="dashicons dashicons-lock" style="font-size:48px;color:#f59e0b;margin-bottom:15px;display:block;"></span>' . '<p style="font-size:14px;color:#475569;margin:0 0 20px 0;line-height:1.6;">' . $managed_text . '</p>' . '<a href="' . \esc_url($reviewx_reviews_url) . '" class="button button-primary button-hero" style="background-color:#6366f1;border-color:#6366f1;font-size:14px;">' . \esc_html__('Go to ReviewX Reviews', 'reviewx') . '</a>' . '<br><br>' . '<a href="' . \esc_url($comments_url) . '" style="color:#666;font-size:13px;text-decoration:none;">' . \esc_html__('&larr; Back to Comments', 'reviewx') . '</a>' . '</div>';
        \wp_die(\wp_kses_post($message), \esc_html__('Review Editing Disabled', 'reviewx'), array('response' => 403, 'back_link' => \true));
    }
}

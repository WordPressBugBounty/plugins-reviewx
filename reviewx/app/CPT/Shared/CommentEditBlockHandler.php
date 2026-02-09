<?php

namespace Rvx\CPT\Shared;

use Rvx\CPT\CptHelper;
use Rvx\Utilities\Auth\Client;
/**
 * Blocks editing of comments/reviews for post types that are managed by ReviewX.
 * This applies to both WP Comment Edit page and WooCommerce Review Edit page.
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
        // Get enabled post types from ReviewX
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
            $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
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
        $comment_id = isset($_GET['c']) ? absint($_GET['c']) : 0;
        if (!$comment_id) {
            return null;
        }
        return get_comment($comment_id);
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
        $post_type = get_post_type($comment->comment_post_ID);
        $enabled_post_types = $this->cptHelper->enabledCPT();
        return isset($enabled_post_types[$post_type]);
    }
    /**
     * Display a notice on the comment edit page that editing is blocked.
     */
    public function displayEditBlockedNotice() : void
    {
        if (!is_admin()) {
            return;
        }
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
        $reviewx_reviews_url = admin_url('admin.php?page=reviewx_reviews');
        $post_type = get_post_type($comment->comment_post_ID);
        $post_type_label = \ucfirst($post_type);
        // Use admin_footer to inject HTML that won't be affected by page load scripts
        add_action('admin_footer', function () use($reviewx_reviews_url, $post_type_label) {
            ?>
            <style>
                /* Position the content area as relative for absolute positioning */
                #wpbody-content {
                    position: relative !important;
                }
                /* Overlay covers only the content area */
                #rvx-edit-blocked-overlay {
                    position: absolute !important;
                    top: 0 !important;
                    left: 0 !important;
                    right: 0 !important;
                    bottom: 0 !important;
                    min-height: 100vh;
                    background: rgba(255,255,255,0.98) !important;
                    z-index: 99 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                }
            </style>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var container = document.getElementById('wpbody-content');
                    if (!container) return;
                    
                    var overlay = document.createElement('div');
                    overlay.id = 'rvx-edit-blocked-overlay';
                    overlay.innerHTML = `
                        <div style="
                            background: #fff;
                            border: 1px solid #c3c4c7;
                            border-left: 4px solid #f59e0b;
                            padding: 30px 40px;
                            max-width: 500px;
                            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                            border-radius: 4px;
                            text-align: center;
                        ">
                            <span class="dashicons dashicons-lock" style="font-size: 48px; color: #f59e0b; margin-bottom: 15px; display: block;"></span>
                            <h2 style="margin: 0 0 15px 0; color: #1e293b; font-size: 20px;">
                                <?php 
            esc_html_e('Review Editing Disabled', 'reviewx');
            ?>
                            </h2>
                            <p style="font-size: 14px; color: #475569; margin: 0 0 20px 0; line-height: 1.6;">
                                <?php 
            \printf(esc_html__('This %s review is managed by ReviewX. To edit this review, please use the ReviewX Reviews page.', 'reviewx'), esc_html($post_type_label));
            ?>
                            </p>
                            <a href="<?php 
            echo esc_url($reviewx_reviews_url);
            ?>" class="button button-primary button-hero" style="background-color: #6366f1; border-color: #6366f1; font-size: 14px;">
                                <?php 
            esc_html_e('Go to ReviewX Reviews', 'reviewx');
            ?>
                            </a>
                            <br><br>
                            <a href="<?php 
            echo esc_url(admin_url('edit-comments.php'));
            ?>" style="color: #666; font-size: 13px; text-decoration: none;">
                                <?php 
            esc_html_e('← Back to Comments', 'reviewx');
            ?>
                            </a>
                        </div>
                    `;
                    container.appendChild(overlay);
                });
            </script>
            <?php 
        });
    }
    /**
     * Redirect to ReviewX reviews page if trying to edit a ReviewX-managed comment.
     * This is an alternative approach using JavaScript for better UX.
     */
    public function maybeRedirectFromEditPage() : void
    {
        if (!is_admin()) {
            return;
        }
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
        // Add inline script to disable form submission
        add_action('admin_footer', function () {
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Disable all form submissions on this page
                    var forms = document.querySelectorAll('form');
                    forms.forEach(function(form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            alert('<?php 
            esc_html_e('This review is managed by ReviewX. Please edit it from the ReviewX Reviews page.', 'reviewx');
            ?>');
                            return false;
                        });
                    });
                    
                    // Disable submit buttons
                    var submitButtons = document.querySelectorAll('input[type="submit"], button[type="submit"]');
                    submitButtons.forEach(function(btn) {
                        btn.disabled = true;
                        btn.style.opacity = '0.5';
                        btn.style.cursor = 'not-allowed';
                    });
                });
            </script>
            <?php 
        });
    }
}

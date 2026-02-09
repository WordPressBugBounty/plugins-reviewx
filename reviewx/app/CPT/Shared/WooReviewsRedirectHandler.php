<?php

namespace Rvx\CPT\Shared;

use Rvx\CPT\CptHelper;
use Rvx\Utilities\Auth\Client;
/**
 * Handles WooCommerce Reviews page redirection notice and hides reviews
 * when product post type is managed by ReviewX.
 */
class WooReviewsRedirectHandler
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    /**
     * Check if WooCommerce product reviews should be hidden.
     *
     * @return bool
     */
    public function shouldHideWooReviews() : bool
    {
        // Check if WooCommerce is active
        if (!\class_exists('WooCommerce')) {
            return \false;
        }
        // Check if sync is completed
        if (!Client::getSync()) {
            return \false;
        }
        // Check if product post type is enabled in ReviewX
        $enabled_post_types = $this->cptHelper->enabledCPT();
        return isset($enabled_post_types['product']);
    }
    /**
     * Check if we're on the WooCommerce Reviews page.
     * WooCommerce Reviews page: edit.php?post_type=product&page=product-reviews
     * Legacy Comments page: edit-comments.php?post_type=product or edit-comments.php?comment_type=review
     *
     * @return bool
     */
    public function isWooReviewsPage() : bool
    {
        global $pagenow;
        // Check for WooCommerce product-reviews page (Products -> Reviews)
        // URL: edit.php?post_type=product&page=product-reviews
        if ($pagenow === 'edit.php') {
            $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
            $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
            if ($post_type === 'product' && $page === 'product-reviews') {
                return \true;
            }
        }
        // Also check legacy edit-comments.php page
        if ($pagenow === 'edit-comments.php') {
            $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
            $comment_type = isset($_GET['comment_type']) ? sanitize_text_field($_GET['comment_type']) : '';
            if ($post_type === 'product' || $comment_type === 'review') {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Display a notice on the WooCommerce Reviews page redirecting users to ReviewX.
     */
    public function displayRedirectNotice() : void
    {
        if (!is_admin()) {
            return;
        }
        if (!$this->shouldHideWooReviews()) {
            return;
        }
        if (!$this->isWooReviewsPage()) {
            return;
        }
        $reviewx_reviews_url = admin_url('admin.php?page=reviewx_reviews');
        ?>
        <div class="notice notice-info" style="padding: 15px; margin: 20px 0; border-left-color: #6366f1;">
            <h3 style="margin: 0 0 10px 0; color: #1e293b;">
                <span class="dashicons dashicons-star-filled" style="color: #6366f1; margin-right: 5px;"></span>
                <?php 
        esc_html_e('Reviews Managed by ReviewX', 'reviewx');
        ?>
            </h3>
            <p style="font-size: 14px; color: #475569; margin: 0 0 15px 0;">
                <?php 
        esc_html_e('Product reviews are now managed by ReviewX. You can view, manage, and respond to all product reviews from the ReviewX Reviews page.', 'reviewx');
        ?>
            </p>
            <a href="<?php 
        echo esc_url($reviewx_reviews_url);
        ?>" class="button button-primary" style="background-color: #6366f1; border-color: #6366f1;">
                <span class="dashicons dashicons-arrow-right-alt" style="margin-top: 4px;"></span>
                <?php 
        esc_html_e('Go to ReviewX Reviews', 'reviewx');
        ?>
            </a>
        </div>
        <style>
            /* Hide the reviews table when ReviewX is managing reviews */
            .wp-list-table.comments,
            .wp-list-table.reviews,
            .tablenav,
            .subsubsub,
            .search-box,
            #comments-form > p:first-child,
            .woocommerce-reviews-table,
            .wc-admin-review-activity-card,
            #the-comment-list,
            .comment-ays,
            form#comments-form {
                display: none !important;
            }
        </style>
        <?php 
    }
    /**
     * Filter WooCommerce product reviews from the comments query using comments_clauses.
     * This is the proper WordPress filter for modifying comment queries.
     *
     * @param array             $clauses       A compacted array of comment query clauses.
     * @param \WP_Comment_Query $comment_query The WP_Comment_Query instance.
     * @return array Modified clauses array.
     */
    public function filterWooReviewsClauses($clauses, $comment_query) : array
    {
        if (!is_admin()) {
            return $clauses;
        }
        if (!$this->shouldHideWooReviews()) {
            return $clauses;
        }
        if (!$this->isWooReviewsPage()) {
            return $clauses;
        }
        // Return no results by adding an impossible WHERE condition
        $clauses['where'] .= ' AND 1=0 ';
        return $clauses;
    }
}

<?php

namespace ReviewX\Providers;

\defined('ABSPATH') || exit;
use ReviewX\CPT\CptAverageRating;
use ReviewX\CPT\CptCommentsLinkMeta;
use ReviewX\CPT\CptRichSchemaHandler;
// use ReviewX\CPT\CommentsRatingColumn;
use ReviewX\CPT\Shared\CommentsReviewsMetaBoxRemover;
use ReviewX\CPT\Shared\CommentsReviewsFilter;
use ReviewX\CPT\Shared\CommentsReviewsRowActionRemover;
use ReviewX\CPT\Shared\WooReviewsRedirectHandler;
use ReviewX\CPT\Shared\CommentEditBlockHandler;
use ReviewX\CPT\Shared\CptPostHandler;
use ReviewX\CPT\Shared\PostsRatingColumn;
use ReviewX\Api\ReviewsApi;
use ReviewX\Utilities\Auth\Client;
use ReviewX\Form\ReviewForm;
use ReviewX\Handlers\BulkAction\CustomBulkActionsForReviewsHandler;
use ReviewX\Handlers\BulkAction\RegisterBulkActionsForReviewsHandler;
use ReviewX\Handlers\BulkAction\ReviewTrashScreenHandler;
use ReviewX\Handlers\CategoryCreateHandler;
use ReviewX\Handlers\CategoryDeleteHandler;
use ReviewX\Handlers\CategoryUpdateHandler;
use ReviewX\Handlers\Customize\WidgetCustomizeOptionsHandler;
use ReviewX\Handlers\Customize\WidgetCustomizeOutputCSSHandler;
use ReviewX\Handlers\MigrationRollback\UpgradeDBSettings;
use ReviewX\Handlers\Notice\ReviewxAdminNoticeHandler;
use ReviewX\Handlers\OrderCreateHandler;
use ReviewX\Handlers\OrderDeleteHandler;
use ReviewX\Handlers\OrderStatusChangedHandler;
use ReviewX\Handlers\OrderUpdateHandler;
use ReviewX\Handlers\OrderUpdateProcessHandler;
use ReviewX\Handlers\PluginRemovalHandler;
use ReviewX\Handlers\Product\ProductImportHandler;
use ReviewX\Handlers\Product\ProductUntrashHandler;
use ReviewX\Handlers\ProductDeleteHandler;
use ReviewX\Handlers\ReplyCommentHandler;
use ReviewX\Handlers\RichSchema\WoocommerceRichSchemaHandler;
use ReviewX\Handlers\ReviewXInit\PageBuilderHandler;
use ReviewX\Handlers\ReviewXInit\ResetProductMetaHandler;
use ReviewX\Handlers\ReviewXInit\ReviewXoldPluginDeactivateHandler;
use ReviewX\Handlers\ReviewXInit\UpgradeReviewxDeactiveProHandler;
use ReviewX\Handlers\UserDeleteHandler;
use ReviewX\Handlers\UserHandler;
use ReviewX\Handlers\UserUpdateHandler;
use ReviewX\Handlers\WChooks\StorefrontReviewLinkClickScroll;
use ReviewX\Handlers\WcTemplates\WcAccountDetails;
use ReviewX\Handlers\WcTemplates\WcAccountFormTag;
use ReviewX\Handlers\WcTemplates\WcEditAccountForm;
use ReviewX\Handlers\WcTemplates\WcSendEmailPermissionHandler;
use ReviewX\Handlers\WcTemplates\WoocommerceLocateTemplateHandler;
use ReviewX\Handlers\WooCommerceReviewEditForm;
use ReviewX\Handlers\WoocommerceSettingsSaveHandler;
use ReviewX\Handlers\WooReviewTableHandler;
use ReviewX\Models\Site;
use ReviewX\Utilities\Auth\ClientManager;
use ReviewX\Utilities\Auth\WpUserManager;
use ReviewX\Utilities\Auth\WpUser;
use ReviewX\Utilities\UploadMimeSupport;
use ReviewX\Services\ReviewService;
use ReviewX\Services\ImportExportServices;
use ReviewX\Services\CacheServices;
use ReviewX\WPDrill\ServiceProvider;
// use ReviewX\Handlers\WcTemplates\WcAccountDetailsError;
class PluginServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(ClientManager::class, function () {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rvx_sites';
            $site = null;
            $table_exists = \wp_cache_get('rvx_sites_table_exists', 'reviewx');
            if (\false === $table_exists) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table existence check is cached
                $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;
                \wp_cache_set('rvx_sites_table_exists', $table_exists, 'reviewx', 3600);
                // Cache for 1 hour
            }
            if ($table_exists) {
                $site = Site::first();
            }
            return new ClientManager($site);
        });
        $this->plugin->bind(WpUserManager::class, function () {
            return new WpUserManager();
        });
    }
    public function boot() : void
    {
        UploadMimeSupport::bootstrapGlobalHooks();
        \add_action(ImportExportServices::IMPORT_EVENT_HOOK, [new ImportExportServices(), 'processScheduledImport'], 10, 1);
        \add_action('plugins_loaded', function () {
            if (\is_admin() && \get_transient('rvx_reset_sync_flag')) {
                (new \ReviewX\Handlers\IsAlreadySyncSucess())->resetSyncFlag();
            }
            require_once \ABSPATH . 'wp-admin/includes/image.php';
        }, 15);
        \add_action('rest_api_init', function () {
            WpUser::setLoggedInStatus(\is_user_logged_in());
            WpUser::setAbility(\is_user_logged_in() && (\current_user_can('manage_options') || \current_user_can('edit_others_posts') || \current_user_can('manage_woocommerce')) ? \true : \false);
        }, 5);
        \add_action('init', new ReviewXoldPluginDeactivateHandler(), 10);
        \add_action('init', new PageBuilderHandler(), 20);
        // add_action('upgrader_process_complete', new ResetProductMetaHandler(), 5, 2);
        \add_action('upgrader_process_complete', new UpgradeReviewxDeactiveProHandler(), 10, 2);
        // add_action('admin_notices', [new ReviewxAdminNoticeHandler(), 'adminNoticeHandler']);
        // add_action('wp_ajax_rvx_dismiss_notice', [new ReviewxAdminNoticeHandler(), 'rvx_admin_deal_notice_until']);
        // Upgrade the WP DB to new v2.1.6
        \add_action('admin_init', [new UpgradeDBSettings(), 'run_upgrade']);
        \add_action('wp_trash_post', new ProductDeleteHandler(), 10, 1);
        \add_action('untrash_post', new ProductUntrashHandler(), 10, 1);
        \add_action('woocommerce_new_order', new OrderCreateHandler());
        \add_action('woocommerce_order_status_changed', new OrderStatusChangedHandler(), 10, 4);
        \add_action('woocommerce_delete_order', new OrderDeleteHandler());
        /**
         * Category Hook
         */
        \add_action('create_term', new CategoryCreateHandler());
        \add_action('delete_term', [new CategoryDeleteHandler(), 'deleteHandler'], 10, 5);
        \add_action('edited_term', new CategoryUpdateHandler());
        /**
         * Customer Hook
         */
        \add_action('user_register', new UserHandler());
        \add_action('delete_user', new UserDeleteHandler());
        \add_action('profile_update', new UserUpdateHandler());
        \add_action('woocommerce_update_order', new OrderUpdateHandler(), 10, 1);
        \add_action('process_order_update', new OrderUpdateProcessHandler(), 20);
        /**
         * Importd product
         */
        \add_action('woocommerce_product_import_inserted_product_object', new ProductImportHandler(), 20, 2);
        /**
         * Woocommerce Hooks
         */
        \add_action('wp_footer', [new StorefrontReviewLinkClickScroll(), 'addScrollScript'], 10, 2);
        /**
         * Woocommerce review table sync with saas
         */
        \add_action('transition_comment_status', new WooReviewTableHandler(), 10, 3);
        /**
         * Woocommerce review replay comments
         */
        \add_action('comment_post', new ReplyCommentHandler(), 10, 3);
        /**
         * CPT Posts - Create/Update
         */
        \add_action('save_post', new CptPostHandler(), 10, 3);
        // Ensure rating is calculated BEFORE sync (priority 9 < 10)
        \add_action('save_post', [CptAverageRating::class, 'update_average_rating'], 9, 3);
        // add_action('transition_post_status', new ProductHandler(), 10, 3);
        // add_action('woocommerce_update_product', new ProductUpdateHandler());
        // Remove Comment / Review Meta box from Add/Edit page (post/product)
        \add_action('add_meta_boxes', [new CommentsReviewsMetaBoxRemover(), 'removeCommentsReviewsMetaBox'], 99);
        // Remove the 'comment_row_actions' filter
        \add_filter('comment_row_actions', [new CommentsReviewsRowActionRemover(), 'removeCommentsReviewsRowActions'], 999, 2);
        // Filter comments for ReviewX-enabled post types from WP Comments admin page
        \add_action('pre_get_comments', [new CommentsReviewsFilter(), 'filterCommentsForReviewxPostTypes'], 10);
        // WooCommerce Reviews page redirect notice and filtering (Products -> Reviews)
        \add_action('admin_notices', [new WooReviewsRedirectHandler(), 'displayRedirectNotice']);
        \add_filter('comments_clauses', [new WooReviewsRedirectHandler(), 'filterWooReviewsClauses'], 100, 2);
        // Block editing of comments/reviews for ReviewX-enabled post types on WP Comment Edit page
        \add_action('admin_init', [new CommentEditBlockHandler(), 'blockCommentEditPage']);
        // Add the new column for rating
        // add_filter('manage_edit-comments_columns', [new CommentsRatingColumn(), 'addRatingColumn']);
        // Populate the new column with rating data
        // add_action('manage_comments_custom_column', [new CommentsRatingColumn(), 'populateRatingColumn'], 10, 2);
        // Add sorting functionality to comments (ReviewX Rating) Column
        //add_filter('manage_edit-comments_sortable_columns', [new CommentsRatingColumn(), 'makeRatingColumnSortable']);
        // add_action('pre_get_comments', [new CommentsRatingColumn(), 'sortCommentsByRating']);
        // Rating column for CPT/ Product
        // Hook into the admin_init action to instantiate the PostsRatingColumn class
        \add_action('admin_init', [new PostsRatingColumn(), 'addColumn']);
        /**
         * CPT comments / reviews
         */
        \add_action('wp_insert_comment', function ($comment_id, $comment) {
            if (ImportExportServices::shouldSuspendCommentSideEffects()) {
                return;
            }
            if ($comment && $comment->comment_post_ID) {
                // 1. Update average rating locally
                CptAverageRating::update_average_rating($comment->comment_post_ID);
                // 2. Sync updated post data to SaaS
                $post = \get_post($comment->comment_post_ID);
                if ($post) {
                    (new CptPostHandler())->__invoke($post->ID, $post, \true);
                }
            }
        }, 999, 2);
        \add_action('comment_post', function ($comment_id, $comment_approved, $comment) {
            if (ImportExportServices::shouldSuspendCommentSideEffects()) {
                return;
            }
            // 1. Update average rating locally
            CptAverageRating::handle_comment_rating($comment_id, $comment_approved, $comment);
            // 2. Sync updated post data to SaaS if we have a comment object
            if ($comment) {
                $post_id = $comment->comment_post_ID;
                $post = \get_post($post_id);
                if ($post) {
                    (new CptPostHandler())->__invoke($post->ID, $post, \true);
                }
            }
        }, 999, 3);
        \add_action('get_comments_number', [new CptCommentsLinkMeta(), 'replace_total_comments_count'], 999, 2);
        \add_action('edit_comment', function ($comment_id) {
            $comment = \get_comment($comment_id);
            if ($comment && $comment->comment_post_ID) {
                // 1. Update average rating locally
                CptAverageRating::update_average_rating($comment->comment_post_ID);
                // 2. Sync updated post data to SaaS
                $post = \get_post($comment->comment_post_ID);
                if ($post) {
                    (new CptPostHandler())->__invoke($post->ID, $post, \true);
                }
            }
        }, 999);
        \add_action('wp_set_comment_status', function ($comment_id, $status) {
            // 1. Update average rating locally
            CptAverageRating::handle_comment_status_change($comment_id, $status);
            // 2. Sync updated post data to SaaS
            $comment = \get_comment($comment_id);
            if ($comment) {
                $post = \get_post($comment->comment_post_ID);
                if ($post) {
                    (new CptPostHandler())->__invoke($post->ID, $post, \true);
                }
            }
        }, 999, 2);
        // Removed redundant save_post hook for CptAverageRating as it's now registered above with correct priority
        \add_action('deleted_comment', function ($comment_id, $comment) {
            if ($comment && $comment->comment_post_ID) {
                if (!ReviewService::shouldSkipDeletedCommentSync()) {
                    // 1. Sync deletion to SaaS
                    $reviewsApi = new ReviewsApi();
                    if ($comment->comment_parent > 0) {
                        // It's a reply
                        $wpUniqueId = Client::getUid() . '-' . $comment->comment_parent;
                        $reviewsApi->deleteCommentReply($wpUniqueId);
                    } else {
                        // It's a review
                        $wpUniqueId = Client::getUid() . '-' . $comment_id;
                        $reviewsApi->deleteReviewData($wpUniqueId);
                    }
                }
                // 2. Update average rating locally
                CptAverageRating::update_average_rating($comment->comment_post_ID);
                // 3. Sync updated post data to SaaS
                $post = \get_post($comment->comment_post_ID);
                if ($post) {
                    (new CptPostHandler())->__invoke($post->ID, $post, \true);
                }
                $cacheServices = new \ReviewX\Services\CacheServices();
                $cacheServices->removeCache();
                \delete_transient("rvx_{$comment->comment_post_ID}_latest_reviews");
                \delete_transient("rvx_{$comment->comment_post_ID}_latest_reviews_insight");
            }
        }, 999, 2);
        /**
         * Rich Schema
         */
        if (!\is_admin()) {
            \add_action('wp_head', [CptRichSchemaHandler::class, 'addCustomRichSchema'], 10, 2);
            \add_action('woocommerce_structured_data_product', new WoocommerceRichSchemaHandler(), 10, 2);
        }
        /**
         * Woocommerce Comment status
         */
        // add_action('wp_set_comment_status', new WoocommerceCommentStatusChangeHandler(), 10, 2);
        \add_filter('bulk_actions-edit-comments', new CustomBulkActionsForReviewsHandler());
        \add_filter('handle_bulk_actions-edit-comments', new RegisterBulkActionsForReviewsHandler(), 10, 3);
        \add_action('admin_init', [new ReviewTrashScreenHandler(), 'maybeHandleEmptyTrash'], 15);
        \add_action('admin_head', [new ReviewTrashScreenHandler(), 'styleEmptyTrashButton']);
        \add_action('admin_footer', [new ReviewTrashScreenHandler(), 'printActionLoaderScript']);
        \add_action('woocommerce_settings_save_products', [new WoocommerceSettingsSaveHandler(), 'wooProductSaveHandler'], 10);
        /**
         * Woocommerce Edit Comment/Review
         */
        \add_action('edit_comment', new WooCommerceReviewEditForm(), 10, 2);
        /**
         * Woocommerce Template Modify
         */
        \add_filter('woocommerce_locate_template', new WoocommerceLocateTemplateHandler(), 10, 3);
        //Woocommerce Avatar
        \add_action('woocommerce_edit_account_form', new WcEditAccountForm(), 10);
        // add_action('woocommerce_save_account_details_errors', new WcAccountDetailsError(), 10, 1);
        \add_action('woocommerce_save_account_details', new WcAccountDetails(), 20, 1);
        \add_action('woocommerce_edit_account_form_tag', new WcAccountFormTag(), 20, 1);
        \add_filter('woocommerce_checkout_fields', new WcSendEmailPermissionHandler(), 20, 1);
        /*
         * Load Appearance -> Customize - ReviewX
         */
        \add_action('customize_register', new WidgetCustomizeOptionsHandler(), 10);
        \add_action('wp_head', new WidgetCustomizeOutputCSSHandler(), 20);
        /*
         * Comment / Review Form Injection on Front-end
         */
        \add_action('init', [ReviewForm::class, 'post_type_support']);
        \add_filter('comments_template', [ReviewForm::class, 'comments_template_init'], \PHP_INT_MAX);
        // Load plugin textdomain removed - handled by WordPress.org
        // Defer localization until scripts are enqueued
        \add_action('init', [$this, 'schedulePendingReviewNoticeSummarySync'], 20);
        \add_action(CacheServices::PENDING_REVIEW_NOTICE_SYNC_HOOK, [$this, 'refreshPendingReviewNoticeSummarySync']);
        \add_action('admin_enqueue_scripts', [$this, 'localizeScripts'], 20);
        \add_action('admin_enqueue_scripts', [$this, 'enqueuePendingReviewNoticeScript'], 20);
        \add_action('wp_enqueue_scripts', [$this, 'localizeScripts'], 20);
        \add_action('wp_ajax_rvx_pending_review_summary', [$this, 'handlePendingReviewSummaryAjax']);
    }
    public function localizeScripts() : void
    {
        $pendingReviewNotice = $this->getPendingReviewNoticeConfig();
        $locals = ['rvx_localization_data_for_admin' => \ReviewX\Utilities\Helper::prepareLangArray(), 'rvx_full_domain_name' => \ReviewX\Utilities\Helper::domainSupport(), 'rvx_full_domain_api' => \ReviewX\Utilities\Helper::getRestAPIurl(), 'rvx_pending_review_notice' => $pendingReviewNotice];
        // Localize for admin handles if they exist
        wp_localize_script('rvx_user_access_script', 'rvx_locals', $locals);
        // Localize for frontend handles if needed
        wp_localize_script('reviewx-storefront', 'rvx_locals', $locals);
    }
    public function enqueuePendingReviewNoticeScript() : void
    {
        if (!\is_admin() || !$this->canAccessPendingReviewNotice()) {
            return;
        }
        wp_enqueue_script('reviewx-admin-pending-review-notice', REVIEWX_URL . 'resources/js/reviewx-admin-pending-review-notice.js', [], REVIEWX_VERSION, \true);
        wp_localize_script('reviewx-admin-pending-review-notice', 'reviewxPendingReviewNotice', $this->getPendingReviewNoticeConfig());
    }
    public function handlePendingReviewSummaryAjax() : void
    {
        if (!$this->canAccessPendingReviewNotice()) {
            wp_send_json_error(['message' => \__('You are not allowed to access pending review summary.', 'reviewx')], 403);
        }
        \check_ajax_referer('reviewx_pending_review_notice');
        $summary = (new CacheServices())->pendingReviewNoticeSummary();
        wp_send_json_success($summary);
    }
    private function getPendingReviewNoticeConfig() : array
    {
        $initialCount = 0;
        if ($this->canAccessPendingReviewNotice()) {
            $summary = (new CacheServices())->pendingReviewNoticeSummary();
            $initialCount = (int) ($summary['pending'] ?? 0);
        }
        return ['ajaxUrl' => \admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('reviewx_pending_review_notice'), 'initialCount' => $initialCount, 'pollInterval' => (int) \apply_filters('reviewx_pending_review_notice_poll_interval', 15000)];
    }
    public function schedulePendingReviewNoticeSummarySync() : void
    {
        if (!Client::has()) {
            return;
        }
        if (!wp_next_scheduled(CacheServices::PENDING_REVIEW_NOTICE_SYNC_HOOK)) {
            wp_schedule_event(\time() + HOUR_IN_SECONDS, 'hourly', CacheServices::PENDING_REVIEW_NOTICE_SYNC_HOOK);
        }
    }
    public function refreshPendingReviewNoticeSummarySync() : void
    {
        if (!Client::has()) {
            return;
        }
        (new CacheServices())->refreshPendingReviewNoticeSummary();
    }
    private function canAccessPendingReviewNotice() : bool
    {
        return Client::has() && (new CacheServices())->currentUserCanAccessReviewx();
    }
}

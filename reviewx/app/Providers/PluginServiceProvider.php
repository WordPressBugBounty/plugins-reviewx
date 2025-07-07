<?php

namespace Rvx\Providers;

use Rvx\CPT\CommentsRatingColumn;
use Rvx\CPT\CptAverageRating;
use Rvx\CPT\CptCommentsLinkMeta;
use Rvx\CPT\CptRichSchemaHandler;
use Rvx\CPT\Shared\CommentsReviewsMetaBoxRemover;
use Rvx\CPT\Shared\CommentsReviewsRowActionRemover;
use Rvx\CPT\Shared\CptPostHandler;
use Rvx\CPT\Shared\PostsRatingColumn;
use Rvx\Form\ReviewForm;
use Rvx\Handlers\BulkAction\CustomBulkActionsForReviewsHandler;
use Rvx\Handlers\BulkAction\RegisterBulkActionsForReviewsHandler;
use Rvx\Handlers\CategoryCreateHandler;
use Rvx\Handlers\CategoryDeleteHandler;
use Rvx\Handlers\CategoryUpdateHandler;
use Rvx\Handlers\Customize\WidgetCustomizeOptionsHandler;
use Rvx\Handlers\Customize\WidgetCustomizeOutputCSSHandler;
use Rvx\Handlers\MigrationRollback\UpgradeDBSettings;
use Rvx\Handlers\Notice\ReviewxAdminNoticeHandler;
use Rvx\Handlers\OrderCreateHandler;
use Rvx\Handlers\OrderDeleteHandler;
use Rvx\Handlers\OrderStatusChangedHandler;
use Rvx\Handlers\OrderUpdateHandler;
use Rvx\Handlers\OrderUpdateProcessHandler;
use Rvx\Handlers\PluginRemovalHandler;
use Rvx\Handlers\Product\ProductImportHandler;
use Rvx\Handlers\Product\ProductUntrashHandler;
use Rvx\Handlers\ProductDeleteHandler;
use Rvx\Handlers\ReplyCommentHandler;
use Rvx\Handlers\RichSchema\WoocommerceRichSchemaHandler;
use Rvx\Handlers\RvxInit\LoadTextDomainHandler;
use Rvx\Handlers\RvxInit\PageBuilderHandler;
use Rvx\Handlers\RvxInit\PermalinkStructureHandler;
use Rvx\Handlers\RvxInit\RedirectReviewxHandler;
use Rvx\Handlers\RvxInit\ResetProductMetaHandler;
use Rvx\Handlers\RvxInit\ReviewXoldPluginDeactivateHandler;
use Rvx\Handlers\RvxInit\UpgradeReviewxDeactiveProHandler;
use Rvx\Handlers\UserDeleteHandler;
use Rvx\Handlers\UserHandler;
use Rvx\Handlers\UserUpdateHandler;
use Rvx\Handlers\WChooks\StorefrontReviewLinkClickScroll;
use Rvx\Handlers\WcTemplates\WcAccountDetails;
use Rvx\Handlers\WcTemplates\WcAccountFormTag;
use Rvx\Handlers\WcTemplates\WcEditAccountForm;
use Rvx\Handlers\WcTemplates\WcSendEmailPermissionHandler;
use Rvx\Handlers\WcTemplates\WoocommerceLocateTemplateHandler;
use Rvx\Handlers\WooCommerceReviewEditForm;
use Rvx\Handlers\WoocommerceSettingsSaveHandler;
use Rvx\Handlers\WooReviewTableHandler;
use Rvx\Models\Site;
use Rvx\Utilities\Auth\ClientManager;
use Rvx\WPDrill\ServiceProvider;
// use Rvx\Handlers\WcTemplates\WcAccountDetailsError;
class PluginServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->plugin->bind(ClientManager::class, function () {
            $site = Site::first();
            return new ClientManager($site);
        });
    }
    public function boot() : void
    {
        add_action('init', new ReviewXoldPluginDeactivateHandler(), 10);
        add_action('init', new PermalinkStructureHandler(), 10);
        add_action('plugins_loaded', function () {
            // Instantiate and register this init hook only after WordPress is loaded
            add_action('init', [new LoadTextDomainHandler(), 'loadLanguage'], 10);
        });
        add_action('activated_plugin', new RedirectReviewxHandler(), 15, 1);
        add_action('plugins_loaded', new PageBuilderHandler(), 20);
        // add_action('upgrader_process_complete', new ResetProductMetaHandler(), 5, 2);
        add_action('upgrader_process_complete', new UpgradeReviewxDeactiveProHandler(), 10, 2);
        // add_action('admin_notices', [new ReviewxAdminNoticeHandler(), 'adminNoticeHandler']);
        // add_action('wp_ajax_rvx_dismiss_notice', [new ReviewxAdminNoticeHandler(), 'rvx_admin_deal_notice_until']);
        // Upgrade the WP DB to new v2.1.6
        add_action('admin_init', [new UpgradeDBSettings(), 'run_upgrade']);
        add_action('wp_trash_post', new ProductDeleteHandler(), 10, 1);
        add_action('untrash_post', new ProductUntrashHandler(), 10, 1);
        add_action('woocommerce_new_order', new OrderCreateHandler());
        add_action('woocommerce_order_status_changed', new OrderStatusChangedHandler(), 10, 4);
        add_action('woocommerce_delete_order', new OrderDeleteHandler());
        /**
         * Category Hook
         */
        add_action('create_term', new CategoryCreateHandler());
        add_action('delete_term', [new CategoryDeleteHandler(), 'deleteHandler'], 10, 5);
        add_action('edited_term', new CategoryUpdateHandler());
        /**
         * Customer Hook
         */
        add_action('user_register', new UserHandler());
        add_action('delete_user', new UserDeleteHandler());
        add_action('profile_update', new UserUpdateHandler());
        add_action('woocommerce_update_order', new OrderUpdateHandler(), 10, 1);
        add_action('process_order_update', new OrderUpdateProcessHandler(), 20);
        /**
         * Importd product
         */
        add_action('woocommerce_product_import_inserted_product_object', new ProductImportHandler(), 20, 2);
        /**
         * Woocommerce Hooks
         */
        add_action('wp_footer', [new StorefrontReviewLinkClickScroll(), 'addScrollScript'], 10, 2);
        /**
         * Woocommerce review table sync with saas
         */
        add_action('transition_comment_status', new WooReviewTableHandler(), 10, 3);
        /**
         * Woocommerce review replay comments
         */
        add_action('comment_post', new ReplyCommentHandler(), 10, 3);
        /**
         * CPT Posts - Create/Update
         */
        add_action('save_post', new CptPostHandler(), 10, 3);
        // add_action('transition_post_status', new ProductHandler(), 10, 3);
        // add_action('woocommerce_update_product', new ProductUpdateHandler());
        // Remove Comment / Review Meta box from Add/Edit page (post/product)
        add_action('add_meta_boxes', [new CommentsReviewsMetaBoxRemover(), 'removeCommentsReviewsMetaBox'], 99);
        // Remove the 'comment_row_actions' filter
        add_filter('comment_row_actions', [new CommentsReviewsRowActionRemover(), 'removeCommentsReviewsRowActions'], 999, 2);
        // Add the new column for rating
        add_filter('manage_edit-comments_columns', [new CommentsRatingColumn(), 'addRatingColumn']);
        // Populate the new column with rating data
        add_action('manage_comments_custom_column', [new CommentsRatingColumn(), 'populateRatingColumn'], 10, 2);
        // Add sorting functionality to comments (ReviewX Rating) Column
        //add_filter('manage_edit-comments_sortable_columns', [new CommentsRatingColumn(), 'makeRatingColumnSortable']);
        // add_action('pre_get_comments', [new CommentsRatingColumn(), 'sortCommentsByRating']);
        // Rating column for CPT/ Product
        // Hook into the admin_init action to instantiate the PostsRatingColumn class
        add_action('admin_init', [new PostsRatingColumn(), 'addColumn']);
        /**
         * CPT comments / reviews
         */
        add_action('wp_insert_comment', function ($comment_id, $comment) {
            if ($comment) {
                CptAverageRating::update_average_rating($comment->comment_post_ID);
            }
        }, 10, 2);
        add_action('comment_post', [CptAverageRating::class, 'handle_comment_rating'], 10, 2);
        add_action('comment_post', [CptAverageRating::class, 'handle_comment_rating'], 10, 3);
        add_action('get_comments_number', [new CptCommentsLinkMeta(), 'replace_total_comments_count'], 10, 2);
        add_action('edit_comment', function ($comment_id) {
            $comment = get_comment($comment_id);
            if ($comment) {
                CptAverageRating::update_average_rating($comment->comment_post_ID);
            }
        });
        add_action('wp_set_comment_status', [CptAverageRating::class, 'handle_comment_status_change'], 10, 2);
        add_action('save_post', [CptAverageRating::class, 'update_average_rating'], 10, 2);
        /**
         * CPT Rich Schema
         * Hook into WordPress frontend to add schema to the post head.
         */
        add_action('wp_head', [CptRichSchemaHandler::class, 'addCustomRichSchema'], 10);
        /**
         * Woocommerce Comment status
         */
        // add_action('wp_set_comment_status', new WoocommerceCommentStatusChangeHandler(), 10, 2);
        add_filter('bulk_actions-edit-comments', new CustomBulkActionsForReviewsHandler());
        add_filter('handle_bulk_actions', new RegisterBulkActionsForReviewsHandler(), 10, 3);
        add_action('woocommerce_settings_save_products', [new WoocommerceSettingsSaveHandler(), 'wooProductSaveHandler'], 10);
        /**
         * Woocommerce Edit Comment/Review
         */
        add_action('edit_comment', new WooCommerceReviewEditForm(), 10, 2);
        /**
         * Woocommerce Rich Schema
         */
        add_action('woocommerce_structured_data_product', new WoocommerceRichSchemaHandler(), 10, 2);
        /**
         * Woocommerce Template Modify
         */
        add_filter('woocommerce_locate_template', new WoocommerceLocateTemplateHandler(), 10, 3);
        //Woocommerce Avater
        add_action('woocommerce_edit_account_form', new WcEditAccountForm(), 10);
        // add_action('woocommerce_save_account_details_errors', new WcAccountDetailsError(), 10, 1);
        add_action('woocommerce_save_account_details', new WcAccountDetails(), 20, 1);
        add_action('woocommerce_edit_account_form_tag', new WcAccountFormTag(), 20, 1);
        add_filter('woocommerce_checkout_fields', new WcSendEmailPermissionHandler(), 20, 1);
        /*
         * Load Appearance -> Customize - ReviewX
         */
        add_action('customize_register', new WidgetCustomizeOptionsHandler(), 10);
        add_action('wp_head', new WidgetCustomizeOutputCSSHandler(), 20);
        /*
         * Comment / Review Form Injection on Front-end
         */
        add_action('init', [ReviewForm::class, 'post_type_support']);
        add_filter('comments_template', [ReviewForm::class, 'comments_template_init'], \PHP_INT_MAX);
    }
}

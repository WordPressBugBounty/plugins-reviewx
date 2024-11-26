<?php

namespace Rvx\Providers;

use Rvx\Handlers\RvxInit\ResetProductMetaHandler;
use Rvx\WPDrill\Plugin;
use Rvx\Models\Site;
use Rvx\WPDrill\ServiceProvider;
use Rvx\Handlers\UserHandler;
use Rvx\Handlers\ProductHandler;
use Rvx\Handlers\CategoryHandler;
use Rvx\Handlers\CommentBoxHandle;
use Rvx\Handlers\UserDeleteHandler;
use Rvx\Handlers\UserUpdateHandler;
use Rvx\Handlers\OrderCreateHandler;
use Rvx\Handlers\OrderDeleteHandler;
use Rvx\Handlers\OrderUpdateHandler;
use Rvx\Utilities\Auth\ClientManager;
use Rvx\Handlers\ProductDeleteHandler;
use Rvx\Handlers\ProductUpdateHandler;
use Rvx\Handlers\ReplayCommentHandler;
use Rvx\Handlers\CategoryDeleteHandler;
use Rvx\Handlers\CategoryUpdateHandler;
use Rvx\Handlers\WooReviewTableHandler;
use Rvx\Handlers\OrderStatusChangedHandler;
use Rvx\Handlers\OrderUpdateProcessHandler;
use Rvx\Handlers\RvxInit\PageBuilderHandler;
use Rvx\Handlers\WcTemplates\WoocommerceInit;
use Rvx\Handlers\Product\ProductImportHandler;
use Rvx\Handlers\WcTemplates\WcAccountDetails;
use Rvx\Handlers\WcTemplates\WcAccountFormTag;
use Rvx\Handlers\Product\ProductUntrashHandler;
use Rvx\Handlers\RvxInit\LoadTextDomainHandler;
use Rvx\Handlers\WcTemplates\WcEditAccountForm;
use Rvx\Handlers\RvxInit\RedirectReviewxHandler;
use Rvx\Handlers\WoocommerceSettingsSaveHandler;
use Rvx\Handlers\WoocommerceCommentUntrashHandler;
use Rvx\Handlers\RvxInit\PermalinkStructureHandler;
use Rvx\Handlers\WcTemplates\WcAccountDetailsError;
use Rvx\Handlers\RvxInit\LoadReviewxCreateSiteTable;
use Rvx\Handlers\WoocommerceCommentMoveToTrashHandler;
use Rvx\Handlers\RichSchma\WoocommerceRichSchmaHandler;
use Rvx\Handlers\WoocommerceCommentStatusChangeHandler;
use Rvx\Handlers\WcTemplates\CommentDefaultTemplateHandler;
use Rvx\Handlers\WcTemplates\WoocommerceLocateTemplateHandler;
use Rvx\Handlers\BulkAction\CustomBulkActionsForReviewsHandler;
use Rvx\Handlers\BulkAction\RegisterBulkActionsForReviewsHandler;
use Rvx\Handlers\Customize\WidgetCustomizeOptionsHandler;
use Rvx\Handlers\Customize\WidgetCustomizeOutputCSSHandler;
use Rvx\Handlers\WcTemplates\WcSendEmailPermissionHandler;
use Rvx\Handlers\WooCommerceReviewEditForm;
use Rvx\Handlers\RvxInit\UpgradeReviewxDeactiveProHandler;
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
        add_action('transition_post_status', new ProductHandler(), 10, 3);
        add_action('woocommerce_update_product', new ProductUpdateHandler());
        add_action('init', new PermalinkStructureHandler(), 10);
        add_action('init', new LoadTextDomainHandler(), 10);
        add_action('activated_plugin', new RedirectReviewxHandler(), 15, 1);
        add_action('plugins_loaded', new PageBuilderHandler(), 20);
        add_action('upgrader_process_complete', new ResetProductMetaHandler(), 5, 2);
        add_action('upgrader_process_complete', new UpgradeReviewxDeactiveProHandler(), 10, 2);
        add_action('wp_trash_post', new ProductDeleteHandler(), 10, 1);
        add_action('untrash_post', new ProductUntrashHandler(), 10, 1);
        add_action('woocommerce_thankyou', new OrderCreateHandler());
        add_action('woocommerce_order_status_changed', new OrderStatusChangedHandler(), 10, 4);
        add_action('plugins_loaded', new LoadReviewxCreateSiteTable(), 10);
        add_action('woocommerce_delete_order', new OrderDeleteHandler());
        /**
         * Category Hook
         */
        add_action('create_term', new CategoryHandler());
        add_action('delete_category', new CategoryDeleteHandler());
        //Post category
        // add_action('delete_product_cat', new CategoryProductDeleteHandler()); //Product category
        add_action('edited_term', new CategoryUpdateHandler());
        /**
         * Customer Hook
         */
        add_action('user_register', new UserHandler());
        add_action('delete_user', new UserDeleteHandler());
        add_action('profile_update', new UserUpdateHandler());
        // add_action('woocommerce_order_status_processing', (new VerifiedUserHandler()));
        add_action('woocommerce_update_order', new OrderUpdateHandler(), 10, 1);
        add_action('process_order_update', new OrderUpdateProcessHandler(), 20);
        /**
         * Importd product
         */
        add_action('woocommerce_product_import_inserted_product_object', new ProductImportHandler(), 20, 2);
        /**
         * Woocommerce review table sync with saas
         */
        add_action('transition_comment_status', new WooReviewTableHandler(), 10, 3);
        /**
         * Woocommerce review replay comments
         */
        add_action('comment_post', new ReplayCommentHandler(), 10, 3);
        /**
         * Woocommerce Comment status
         */
        add_action('wp_set_comment_status', new WoocommerceCommentStatusChangeHandler(), 10, 2);
        add_filter('bulk_actions-edit-comments', new CustomBulkActionsForReviewsHandler());
        add_filter('handle_bulk_actions', new RegisterBulkActionsForReviewsHandler(), 10, 3);
        add_action('trashed_comment', new WoocommerceCommentMoveToTrashHandler());
        add_action('untrash_comment', new WoocommerceCommentUntrashHandler());
        add_action('woocommerce_settings_save_products', new WoocommerceSettingsSaveHandler());
        /**
         * Woocommerce Edit Comment/Review
         */
        add_action('edit_comment', new WooCommerceReviewEditForm(), 10, 2);
        /**
         * Woocommerce Comment status
         */
        add_action('woocommerce_structured_data_product', new WoocommerceRichSchmaHandler(), 10, 2);
        /**
         * Woocommerce Template Modify
         */
        add_filter('woocommerce_locate_template', new WoocommerceLocateTemplateHandler(), 10, 3);
        // add_filter('comments_template', new CommentDefaultTemplateHandler(), 10);
        add_filter('comments_template', new CommentBoxHandle(), 99);
        //Woocommerce Avater
        add_action('woocommerce_edit_account_form', new WcEditAccountForm(), 10);
        add_action('woocommerce_save_account_details_errors', new WcAccountDetailsError(), 10, 1);
        add_action('woocommerce_save_account_details', new WcAccountDetails(), 20, 1);
        add_action('woocommerce_edit_account_form_tag', new WcAccountFormTag(), 20, 1);
        add_filter('woocommerce_checkout_fields', new WcSendEmailPermissionHandler(), 20, 1);
        /*
         * Load Appearance -> Customize - ReviewX
         */
        add_action('customize_register', new WidgetCustomizeOptionsHandler(), 10);
        add_action('wp_head', new WidgetCustomizeOutputCSSHandler(), 20);
    }
}

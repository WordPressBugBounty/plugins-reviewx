<?php

namespace Rvx;

use Rvx\Rest\Middleware\AdminMiddleware;
use Rvx\WPDrill\Facades\Route;
use Rvx\Rest\Middleware\AuthMiddleware;
use Rvx\Rest\Controllers\UserController;
use Rvx\Rest\Controllers\LoginController;
use Rvx\Rest\Controllers\ReviewController;
use Rvx\Rest\Middleware\WPDrillMiddleware;
use Rvx\Rest\Controllers\SettingController;
use Rvx\Rest\Controllers\WPDrillController;
use Rvx\Rest\Controllers\CategoryController;
use Rvx\Rest\Controllers\DiscountController;
use Rvx\Rest\Controllers\RegisterController;
use Rvx\Rest\Controllers\DashboardController;
use Rvx\Rest\Controllers\CustomPostController;
use Rvx\Rest\Controllers\SaveOptionsController;
use Rvx\Rest\Controllers\GoogleReviewController;
use Rvx\Rest\Controllers\ImportExportController;
use Rvx\Rest\Controllers\EmailTemplateController;
use Rvx\Rest\Controllers\Products\ProductController;
use Rvx\Rest\Controllers\StoreFrontReviewController;
use Rvx\Rest\Middleware\DevMiddleware;
use Rvx\Rest\Controllers\AccessController;
use Rvx\Rest\Controllers\DataSyncController;
use Rvx\Rest\Controllers\LogController;
Route::group(['prefix' => '/api/v1'], function (\Rvx\WPDrill\Routing\RouteManager $route) {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/login/key', [LoginController::class, 'license_key']);
    Route::post('/forget/password', [LoginController::class, 'forgetPassword']);
    Route::post('/reset/password', [LoginController::class, 'resetPassword']);
    Route::post('/save_options', [SaveOptionsController::class, 'save_options']);
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/migration/prompt', [RegisterController::class, 'migrationPrompt']);
    Route::post('/user/plan/access', [SettingController::class, 'userSettingsAccess']);
    Route::get('/user/current/plan', [SettingController::class, 'userCurrentPlan']);
});
Route::group(['prefix' => '/api/v1', 'middleware' => AuthMiddleware::class], function (\Rvx\WPDrill\Routing\RouteManager $route) {
    Route::post('/admin/access/control', [AccessController::class, 'adminAccess']);
});
Route::group(['prefix' => '/api/v1', 'middleware' => AuthMiddleware::class], function (\Rvx\WPDrill\Routing\RouteManager $route) {
    /**
     * Reviews API
     */
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews/create/manual', [ReviewController::class, 'store']);
    Route::get('/reviews/list', [ReviewController::class, 'reviewList']);
    Route::post('/reviews/bulk/status/update', [ReviewController::class, 'reviewBulkUpdate']);
    Route::post('/reviews/bulk/trash', [ReviewController::class, 'reviewBulkTrash']);
    Route::post('/reviews/trash/(?P<WpUniqueId>[a-zA-Z0-9-]+)', [ReviewController::class, 'reviewMoveToTrash']);
    Route::post('/reviews/empty/trash', [ReviewController::class, 'reviewEmptyTrash']);
    Route::get('/reviews/(?P<wpUniqueId>[a-zA-Z0-9-]+)', [ReviewController::class, 'show']);
    Route::post('/reviews/(?P<wpUniqueId>[a-zA-Z0-9-]+)/update', [ReviewController::class, 'update']);
    Route::post('/reviews/delete/(?P<wpUniqueId>[a-zA-Z0-9-]+)', [ReviewController::class, 'delete']);
    Route::post('/reviews/restore/(?P<wpUniqueId>[a-zA-Z0-9-]+)', [ReviewController::class, 'restoreReview']);
    Route::post('/reviews/(?P<wpUniqueId>[a-zA-Z0-9-]+)/verify', [ReviewController::class, 'verify']);
    Route::post('/reviews/(?P<wpUniqueId>[a-zA-Z0-9-]+)/visibility', [ReviewController::class, 'visibility']);
    Route::post('/reviews/(?P<wpUniqueId>[a-zA-Z0-9-]+)/send/update/request/email', [ReviewController::class, 'updateReqEmail']);
    Route::post('/reviews/bulk/status/update', [ReviewController::class, 'reviewBulkUpdate']);
    Route::get('/reviews/get/aggregation', [ReviewController::class, 'reviewAggregation']);
    Route::get('/wp/products', [ProductController::class, 'wpProducts']);
    Route::get('/products/selectable', [ProductController::class, 'selectable']);
    Route::post('/reviews/(?P<wpUniqueId>[a-zA-Z0-9-]+)/highlight', [ReviewController::class, 'highlight']);
    Route::post('/reviews/bulk/ten/response', [ReviewController::class, 'bulkTenReviews']);
    Route::post('/reviews/bulk/action/product/meta', [ReviewController::class, 'bulkActionProductMeta']);
    Route::post('bulk/restore/trash', [ReviewController::class, 'restoreTrashItem']);
    /**
     * MultiCritriya 
     */
    Route::get('reviews/list/multi/criteria', [ReviewController::class, 'reviewListMultiCriteria']);
    Route::post('/reviews/(?P<wpUniqueId>[a-zA-Z0-9-]+)/replies', [ReviewController::class, 'replies']);
    Route::post('/reviews/(?P<wpUniqueId>[a-zA-Z0-9-]+)/update/replies', [ReviewController::class, 'repliesUpdate']);
    Route::post('/reviews/(?P<wpUniqueId>[a-zA-Z0-9-]+)/delete/reply', [ReviewController::class, 'replyDelete']);
    /**
     * Reviews API
     */
    Route::post('/reviews/create/ai', [ReviewController::class, 'aiReview']);
    Route::get('reviews/ai/count', [ReviewController::class, 'aiReviewCount']);
    Route::post('/reviews/product/aggregation/meta', [ReviewController::class, 'aggregationMeta']);
    /**
     * Reviews Import and Export
     */
    Route::get('/admin/import/history', [ImportExportController::class, 'importHistory']);
    Route::post('/admin/import/supported/app/store', [ImportExportController::class, 'importSupportedAppStore']);
    Route::post('/reviews/import/store', [ImportExportController::class, 'importStore']);
    Route::post('/admin/import/rollback/(?P<uid>[a-zA-Z0-9-]+)', [ImportExportController::class, 'importRollback']);
    Route::get('/reviews/exports/history', [ImportExportController::class, 'exportHistory']);
    Route::post('/reviews/exports/generate/csv', [ImportExportController::class, 'exportCsv']);
    /**
     * Dashboard insight reviews
     */
    Route::get('/insight/reviews', [DashboardController::class, 'insight']);
    Route::get('/insight/review/request/email', [DashboardController::class, 'requestEmail']);
    Route::get('/dashboard/chart', [DashboardController::class, 'chart']);
    /**
     * Reviewx v1 Settings
     */
    // Route::get('/reviewx/wp/settings', [SettingController::class, 'reviewxSettings']);
    /**
     * Review Settings
     */
    Route::get('/reviews/settings/get', [SettingController::class, 'getReviewSettings']);
    Route::post('/reviews/settings/save', [SettingController::class, 'saveReviewSettings']);
    /**
     * Widget Settings
     */
    Route::get('/settings/widget/get', [SettingController::class, 'getWidgetSettings']);
    Route::post('/settings/widget/save', [SettingController::class, 'saveWidgetSettings']);
    /**
     * Remove table and user information
     */
    Route::post('/user/credentials/remove', [SettingController::class, 'removeCredentials']);
    /**
     * Genaral Settings
     */
    Route::get('/settings/general/get', [SettingController::class, 'getGeneralSettings']);
    Route::post('/settings/general/save', [SettingController::class, 'saveGeneralSettings']);
    /**
     * Woocommerc Product Settings
     */
    Route::get('/woo/review/rating/verification/label', [SettingController::class, 'wooCommerceVerificationRating']);
    Route::post('/woo/review/rating/verification/change', [SettingController::class, 'wooCommerceVerificationRatingUpdate']);
    Route::get('/woo/review/rating/verification/required', [SettingController::class, 'wooVerificationRatingRequired']);
    Route::post('/woo/review/rating/verification/required/update', [SettingController::class, 'wooVerificationRating']);
    /**
     * Customer
     */
    Route::get('users', [UserController::class, 'getUser']);
    /**
     * Category
     */
    Route::get('category/selectable', [CategoryController::class, 'selectable']);
    Route::get('categories', [CategoryController::class, 'getCategory']);
    Route::get('category/all', [CategoryController::class, 'getCategoryAll']);
    Route::post('category/store', [CategoryController::class, 'storeCategory']);
    /**
     * Email Template
     */
    Route::get('email/templates', [EmailTemplateController::class, 'index']);
    Route::post('email/templates', [EmailTemplateController::class, 'store']);
    Route::get('email/templates/(?P<id>[a-zA-Z0-9-]+)', [EmailTemplateController::class, 'show']);
    Route::post('email/templates/(?P<id>[a-zA-Z0-9-]+)', [EmailTemplateController::class, 'update']);
    Route::post('email/templates', [EmailTemplateController::class, 'trash']);
    /**
     * Review Request Email Template
     */
    Route::get('review/request/emails', [EmailTemplateController::class, 'mailRequest']);
    Route::get('review/email/contents', [EmailTemplateController::class, 'mailContents']);
    Route::post('review/email/request/contents', [EmailTemplateController::class, 'saveEmailRequest']);
    Route::post('review/email/followup/contents', [EmailTemplateController::class, 'followup']);
    Route::post('review/email/photo/contents', [EmailTemplateController::class, 'photoReview']);
    Route::post('review/email/send/test', [EmailTemplateController::class, 'testMail']);
    /**
     * Review Reminder All request Settings
     */
    Route::get('/review/request/settings', [EmailTemplateController::class, 'reviewRequestSettings']);
    Route::post('/review/request/settings', [EmailTemplateController::class, 'allReminderSettings']);
    Route::post('/review/request/email/mark/done/(?P<uid>[a-zA-Z0-9-]+)', [EmailTemplateController::class, 'markAsDone']);
    Route::post('/review/request/email/cancel/(?P<uid>[a-zA-Z0-9-]+)', [EmailTemplateController::class, 'requestEmailCancel']);
    Route::post('/review/request/email/send/(?P<uid>[a-zA-Z0-9-]+)', [EmailTemplateController::class, 'requestEmailSend']);
    Route::post('/review/request/email/resend/(?P<uid>[a-zA-Z0-9-]+)', [EmailTemplateController::class, 'requestEmailResend']);
    Route::post('/review/request/email/unsubscribe', [EmailTemplateController::class, 'requestEmailUnsubscribe']);
    /**
     * Coupon
     */
    Route::get('discount', [DiscountController::class, 'getDiscount']);
    //form saas
    Route::get('discount/settings', [DiscountController::class, 'discountSetting']);
    //form saas
    Route::post('discount/wp/create', [DiscountController::class, 'wpDiscountCreate']);
    //local
    Route::post('discount/settings', [DiscountController::class, 'discountSettingsSave']);
    //form saas
    Route::post('discount', [DiscountController::class, 'saveDiscount']);
    //form saas
    Route::get('discount/template', [DiscountController::class, 'discountTemplateGet']);
    //form saas
    Route::post('discount/template', [DiscountController::class, 'discountTemplatePost']);
    //form saas
    Route::post('discount/message', [DiscountController::class, 'discountMessage']);
    //form saas
    /**
     * CPT
     */
    /**
     * All Settings
     */
    Route::get('custom/get', [CustomPostController::class, 'customGet']);
    Route::post('custom/store', [CustomPostController::class, 'customStore']);
    Route::post('custom/(?P<uid>[a-zA-Z0-9-]+)/update', [CustomPostController::class, 'customUpdate']);
    Route::post('custom/(?P<uid>[a-zA-Z0-9-]+)/delete', [CustomPostController::class, 'customdelete']);
    Route::post('custom/(?P<uid>[a-zA-Z0-9-]+)/status', [CustomPostController::class, 'customPostStatusChange']);
    // wordpress custom post show this route
    Route::get('custom/wp/get', [CustomPostController::class, 'customWpGet']);
    /**
     * Data sync 
     */
    Route::get('/site/sync/status', [SettingController::class, 'dataSyncStatus']);
    /**
     * Google Review
     */
    Route::get('google/review/get', [GoogleReviewController::class, 'googleReviewGet']);
    Route::post('google/place/key/store', [GoogleReviewController::class, 'googleReviewKey']);
    Route::post('google/place/setting/store', [GoogleReviewController::class, 'googleReviewSetting']);
    Route::get('google/settings/placeapi/get', [GoogleReviewController::class, 'googleReviewPlaceApi']);
    Route::post('storefront/google/recaptcha/verify', [GoogleReviewController::class, 'googleRecaptchaVerify']);
    /**
     * Google Rich Schema
     */
    Route::post('google/rich/schma', [GoogleReviewController::class, 'googleRichSchma']);
    /**
     * Sync
     */
    Route::get('/data/sync', [DataSyncController::class, 'dataSync']);
    Route::get('/sync/status', [DataSyncController::class, 'syncStatus']);
    Route::get('/backend/(?P<product_id>[a-zA-Z0-9-]+)/reviews', [ReviewController::class, 'getSingleProductAllReviews']);
});
Route::group(['prefix' => '/api/v1'], function (\Rvx\WPDrill\Routing\RouteManager $route) {
    Route::post('/site/all/settings', [SettingController::class, 'allSettingsSave']);
    /**
     * Store Front
     */
    Route::get('/storefront/(?P<product_id>[a-zA-Z0-9-]+)/reviews', [StoreFrontReviewController::class, 'getWidgetReviewsForProduct']);
    Route::get('/storefront/(?P<product_id>[a-zA-Z0-9-]+)/insight', [StoreFrontReviewController::class, 'getWidgetInsight']);
    Route::post('/storefront/reviews', [StoreFrontReviewController::class, 'saveWidgetReviewsForProduct']);
    Route::post('/storefront/request/review/email/attachments/items', [StoreFrontReviewController::class, 'requestReviewEmailAttachment']);
    Route::post('data/sync/complete', [DataSyncController::class, 'dataSynComplete']);
    Route::post('reviews/single/action/product/meta', [StoreFrontReviewController::class, 'singleActionProductMata']);
    Route::post('/storefront/reviews/(?P<uniq_id>[a-zA-Z0-9-]+)/preference', [StoreFrontReviewController::class, 'likeDIslikePreference']);
    Route::get('/storefront/(?P<product_id>[a-zA-Z0-9-]+)/wp', [StoreFrontReviewController::class, 'wpLocalStorageData']);
    Route::post('/storefront/request/review/email/(?P<uid>[a-zA-Z0-9-]+)/store/items', [StoreFrontReviewController::class, 'reviewRequestStoreItem']);
    Route::get('/storefront/thanks/message', [StoreFrontReviewController::class, 'thanksMessage']);
    Route::post('/storefront/test', [StoreFrontReviewController::class, 'test']);
    Route::post('/setting/meta', [StoreFrontReviewController::class, 'settingMeta']);
    Route::post('/storefront/widgets/short/code/reviews', [StoreFrontReviewController::class, 'getSpecificReviewItem']);
    //wp setting get form db
    Route::get('/storefront/wp/settings', [StoreFrontReviewController::class, 'getLocalSettings']);
});
Route::group(['prefix' => '/api/v1', 'middleware' => AdminMiddleware::class], function (\Rvx\WPDrill\Routing\RouteManager $route) {
    Route::get('/rvx/error/log/', [LogController::class, 'rvxRecentLog']);
    Route::get('/append/json/', [LogController::class, 'appendJsonSync']);
});

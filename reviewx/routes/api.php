<?php

namespace Rvx;

use Rvx\Rest\Middleware\AdminMiddleware;
use Rvx\WPDrill\Facades\Route;
use Rvx\Rest\Middleware\AuthMiddleware;
use Rvx\Rest\Controllers\UserController;
use Rvx\Rest\Controllers\AuthController;
use Rvx\Rest\Controllers\ReviewController;
use Rvx\Rest\Controllers\SettingController;
use Rvx\Rest\Controllers\CategoryController;
use Rvx\Rest\Controllers\DiscountController;
use Rvx\Rest\Controllers\DashboardController;
use Rvx\Rest\Controllers\GoogleReviewController;
use Rvx\Rest\Controllers\ImportExportController;
use Rvx\Rest\Controllers\EmailTemplateController;
use Rvx\Rest\Controllers\Products\ProductController;
use Rvx\Rest\Controllers\StoreFrontReviewController;
use Rvx\Rest\Controllers\AccessController;
use Rvx\Rest\Controllers\DataSyncController;
use Rvx\Rest\Controllers\LogController;
use Rvx\Rest\Controllers\CptController;
use Rvx\Rest\Controllers\ImportJudgeMeController;
use Rvx\Rest\Controllers\PingController;
use Rvx\Rest\Middleware\AuthSaasMiddleware;
Route::group(['prefix' => '/api/v1'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/key', [AuthController::class, 'license_key']);
    Route::post('/forget/password', [AuthController::class, 'forgetPassword']);
    Route::post('/reset/password', [AuthController::class, 'resetPassword']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/migration/prompt', [AuthController::class, 'migrationPrompt']);
    Route::post('/user/plan/access', [SettingController::class, 'userSettingsAccess']);
    Route::get('/user/current/plan', [SettingController::class, 'userCurrentPlan']);
    Route::post('/site/all/settings', [SettingController::class, 'allSettingsSave']);
    /**
     * Frontend API
     * Store Front
     */
    Route::get('/storefront/(?P<product_id>[a-zA-Z0-9-]+)/reviews', [StoreFrontReviewController::class, 'getWidgetReviewsForProduct']);
    Route::get('/storefront/(?P<product_id>[a-zA-Z0-9-]+)/reviews/shortcode', [StoreFrontReviewController::class, 'getWidgetReviewsListShortcode']);
    Route::get('/storefront/(?P<product_id>[a-zA-Z0-9-]+)/insight', [StoreFrontReviewController::class, 'getWidgetInsight']);
    Route::post('/storefront/reviews', [StoreFrontReviewController::class, 'saveWidgetReviewsForProduct']);
    Route::post('/storefront/request/review/email/attachments/items', [StoreFrontReviewController::class, 'requestReviewEmailAttachment']);
    Route::post('data/sync/complete', [DataSyncController::class, 'dataSynComplete']);
    Route::post('reviews/single/action/product/meta', [StoreFrontReviewController::class, 'singleActionProductMata']);
    Route::post('/storefront/reviews/(?P<uniq_id>[a-zA-Z0-9-]+)/preference', [StoreFrontReviewController::class, 'likeDislikePreference']);
    Route::get('/storefront/(?P<product_id>[a-zA-Z0-9-]+)/wp', [StoreFrontReviewController::class, 'wpLocalStorageData']);
    Route::post('/storefront/request/review/email/(?P<uid>[a-zA-Z0-9-]+)/store/items', [StoreFrontReviewController::class, 'reviewRequestStoreItem']);
    Route::get('/storefront/thanks/message', [StoreFrontReviewController::class, 'thanksMessage']);
    Route::post('/storefront/test', [StoreFrontReviewController::class, 'test']);
    Route::post('/setting/meta', [StoreFrontReviewController::class, 'settingMeta']);
    Route::post('/storefront/widgets/short/code/reviews', [StoreFrontReviewController::class, 'getSpecificReviewItem']);
    //wp setting get form db
    Route::get('/storefront/wp/settings', [StoreFrontReviewController::class, 'getLocalSettings']);
    //ALl review shortcode
    Route::post('/storefront/all/reviews/shortcode', [StoreFrontReviewController::class, 'getWidgetAllReviewsListForSite']);
    // Judgeme API's
    Route::get('/judgeme/status/detect', [ImportJudgeMeController::class, 'judgemeStatusDetect']);
});
Route::group(['prefix' => '/api/v1', 'middleware' => AuthMiddleware::class], function () {
    // Judgeme API's
    Route::post('/judgeme/export/csv', [ImportJudgeMeController::class, 'judgemeCSVdownload']);
    Route::post('/judgeme/upload/csv', [ImportJudgeMeController::class, 'judgemeCSVUpload']);
    Route::post('/judgeme/import/chunk', [ImportJudgeMeController::class, 'judgemeImportChunk']);
    Route::post('/judgeme/data/sync', [ImportJudgeMeController::class, 'judgemeDataSaasSync']);
    Route::get('/judgeme/import/status', [ImportJudgeMeController::class, 'judgemeImportStatus']);
    /**
     * Reviews API
     */
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews/create/manual', [ReviewController::class, 'store']);
    Route::get('/reviews/list', [ReviewController::class, 'reviewList']);
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
    Route::post('bulk/restore/trash', [ReviewController::class, 'restoreTrashItem']);
    /**
     * Multi-Criteria
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
     * Review Settings
     */
    Route::get('/reviews/settings/get', [SettingController::class, 'getApiReviewSettings']);
    Route::post('/reviews/settings/save', [SettingController::class, 'saveApiReviewSettings']);
    /**
     * Widget Settings
     */
    Route::get('/settings/widget/get', [SettingController::class, 'getAPiWidgetSettings']);
    Route::post('/settings/widget/save', [SettingController::class, 'saveApiWidgetSettings']);
    /**
     * General Settings
     */
    Route::get('/settings/general/get', [SettingController::class, 'getApiGeneralSettings']);
    Route::post('/settings/general/save', [SettingController::class, 'saveApiGeneralSettings']);
    /**
     * WooCommerce Product Settings
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
    Route::get('custom/get', [CptController::class, 'cptGet']);
    Route::post('custom/store', [CptController::class, 'cptStore']);
    Route::post('custom/(?P<uid>[a-zA-Z0-9-]+)/update', [CptController::class, 'cptUpdate']);
    Route::post('custom/(?P<uid>[a-zA-Z0-9-]+)/delete', [CptController::class, 'cptDelete']);
    Route::post('custom/(?P<uid>[a-zA-Z0-9-]+)/status', [CptController::class, 'cptStatusChange']);
    // WordPress custom post show this route
    Route::get('custom/wp/get', [CptController::class, 'customPostTypes']);
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
     * Data / CPT Sync
     */
    Route::get('/data/sync', [DataSyncController::class, 'dataSync']);
    Route::get('/sync/status', [DataSyncController::class, 'syncStatus']);
    Route::get('/site/sync/status', [SettingController::class, 'dataSyncStatus']);
    Route::get('/backend/(?P<product_id>[a-zA-Z0-9-]+)/reviews', [ReviewController::class, 'getSingleProductAllReviews']);
});
Route::group(['prefix' => '/api/v1', 'middleware' => AuthSaasMiddleware::class], function () {
    Route::get('/synced/data', [DataSyncController::class, 'syncedData']);
    Route::post('/admin/access/control', [AccessController::class, 'adminAccess']);
    Route::post('/reviews/bulk/ten/response', [ReviewController::class, 'bulkTenReviews']);
    Route::post('/reviews/bulk/action/product/meta', [ReviewController::class, 'bulkActionProductMeta']);
    /**
     * Remove table and user information
     */
    Route::post('/user/credentials/remove', [SettingController::class, 'removeCredentials']);
    /**
     * Plugin meta data gather
     */
    Route::get('/ping', [PingController::class, 'ping']);
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
});
Route::group(['prefix' => '/api/v1', 'middleware' => AdminMiddleware::class], function () {
    Route::get('/rvx/error/log/', [LogController::class, 'rvxRecentLog']);
    Route::get('/append/json/', [LogController::class, 'appendJsonSync']);
    Route::get('/data/manual/sync', [DataSyncController::class, 'dataManualSync']);
});

<?php

namespace Rvx\Services;

use Exception;
use Rvx\Api\DataSyncApi;
use Rvx\Api\WebhookRequestApi;
use Rvx\Handlers\DataSyncHandler;
use Rvx\Services\Service;
use Rvx\Services\OrderService;
use Rvx\Services\OrderItemSyncService;
use Rvx\Services\UserSyncService;
use Rvx\Services\ProductSyncService;
use Rvx\Services\ReviewSyncService;
use Rvx\Services\CategorySyncService;
use Rvx\Utilities\Helper;
class DataSyncService extends Service
{
    protected DataSyncHandler $dataSyncHandler;
    protected UserSyncService $userSyncService;
    protected CategorySyncService $categorySyncService;
    protected ProductSyncService $productSyncService;
    protected ReviewSyncService $reviewSyncService;
    protected OrderService $orderService;
    protected OrderItemSyncService $orderItemSyncService;
    public function __construct()
    {
        $this->dataSyncHandler = new DataSyncHandler();
        $this->userSyncService = new UserSyncService();
        $this->categorySyncService = new CategorySyncService();
        $this->productSyncService = new ProductSyncService();
        $this->reviewSyncService = new ReviewSyncService();
        $this->orderService = new OrderService();
        $this->orderItemSyncService = new OrderItemSyncService();
    }
    public function dataSync($from, $post_type = 'product') : bool
    {
        try {
            $reviewx_dir_exists = \is_dir(WP_CONTENT_DIR . '/uploads/reviewx');
            if (!$reviewx_dir_exists) {
                // Create the directory if it does not exist
                \mkdir(WP_CONTENT_DIR . '/uploads/reviewx', 0777, \true);
            }
            // Create the file path for the sync data
            // Sanitize just in case:
            $post_type = sanitize_key($post_type);
            if ($post_type === 'product') {
                $file_name = "shop-bulk-data.jsonl";
            } else {
                $file_name = "{$post_type}-cpt-bulk-data.jsonl";
            }
            $file_path = WP_CONTENT_DIR . '/uploads/reviewx/' . $file_name;
            $file = \fopen($file_path, 'w');
            $total_objects = 0;
            if ($post_type === 'product') {
                // Product Sync Data for *Login or Register*
                $total_objects += $this->userSyncService->syncUser($file);
                if (\class_exists('WooCommerce') || $this->dataSyncHandler->wc_data_exists_in_db()) {
                    $syncedCaterories = new CategorySyncService();
                    $total_objects += $syncedCaterories->syncCategory($file);
                    $total_objects += $this->productSyncService->processProductForSync($file, $post_type);
                    $total_objects += $this->reviewSyncService->processReviewForSync($file, $post_type);
                    $total_objects += $this->orderItemSyncService->syncOrder($file);
                    $total_objects += $this->orderItemSyncService->syncOrderItem($file);
                }
            } else {
                // CPT Sync Data
                $total_objects += $this->productSyncService->processProductForSync($file, $post_type);
                $total_objects += $this->reviewSyncService->processReviewForSync($file, $post_type);
            }
            \fclose($file);
            (new WebhookRequestApi())->finishedWebhook(['total_objects' => $total_objects, 'status' => 'finished', 'from' => $from, 'post_type' => $post_type, 'resource_url' => Helper::getRestAPIurl() . '/api/v1/synced/data?post_type=' . $post_type]);
            return \true;
        } catch (Exception $e) {
            return \false;
        }
    }
    protected function dataSyncFile($file, $file_path, $from, $total_objects)
    {
        \fclose($file);
        $file_info = $this->prepareFileInfo($file_path);
        $file = $_FILES['file'] = $file_info;
        $fileUpload = (new DataSyncApi())->dataSync($file, $from, $total_objects);
        if (\file_exists($file_path)) {
            \unlink($file_path);
        }
        return $fileUpload;
    }
    private function prepareFileInfo($file_path)
    {
        return ['name' => \basename($file_path), 'full_path' => \realpath($file_path), 'type' => "application/json", 'tmp_name' => $file_path, 'error' => 0, 'size' => \filesize($file_path)];
    }
    public function syncStatus()
    {
        return (new DataSyncApi())->syncStatus();
    }
    public function dataManualSync($data)
    {
        \mkdir(WP_CONTENT_DIR . '/uploads/reviewx', 0777, \true);
        $file_path = WP_CONTENT_DIR . '/uploads/reviewx/manual_sync.jsonl';
        $file = \fopen($file_path, 'a');
        $totalLines = 0;
        if ("users" === $data['action']) {
            $totalLines = get_option('rvx_sync_number');
            $totalLines += (new UserSyncService())->syncUser($file);
            update_option('rvx_sync_number', $totalLines);
        }
        if ("categories" === $data['action']) {
            if (\class_exists('WooCommerce') || $this->dataSyncHandler->wc_data_exists_in_db()) {
                $syncedCaterories = new CategorySyncService();
                $totalLines += $syncedCaterories->syncCategory($file);
                $processProduct = new ProductSyncService($syncedCaterories);
                $totalLines += $processProduct->processProductForSync($file);
                update_option('rvx_sync_number', $totalLines);
            }
        }
        if ("reviews" === $data['action']) {
            if (\class_exists('WooCommerce') || $this->dataSyncHandler->wc_data_exists_in_db()) {
                $totalLines = get_option('rvx_sync_number');
                $totalLines += (new ReviewSyncService())->processReviewForSync($file);
                update_option('rvx_sync_number', $totalLines);
            }
        }
        if ("order" === $data['action']) {
            if (\class_exists('WooCommerce') || $this->dataSyncHandler->wc_data_exists_in_db()) {
                $order = new OrderItemSyncService();
                $totalLines = get_option('rvx_sync_number');
                $totalLines += $order->syncOrder($file);
                $totalLines += $order->syncOrderItem($file);
                update_option('rvx_sync_number', $totalLines);
            }
        }
        if ("api" === $data['action']) {
            $totalLines = get_option('rvx_sync_number');
            return $this->dataSyncFile($file, $file_path, 'register', $totalLines);
        }
    }
}

<?php

namespace Rvx\Services;

\defined("ABSPATH") || exit;
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
// use Rvx\Services\CategorySyncService;
use Rvx\Utilities\Helper;
class DataSyncService extends Service
{
    protected DataSyncHandler $dataSyncHandler;
    protected UserSyncService $userSyncService;
    // protected CategorySyncService $categorySyncService;
    protected ProductSyncService $productSyncService;
    protected ReviewSyncService $reviewSyncService;
    protected OrderService $orderService;
    protected OrderItemSyncService $orderItemSyncService;
    public function __construct()
    {
        $this->dataSyncHandler = new DataSyncHandler();
        $this->userSyncService = new UserSyncService();
        // $this->categorySyncService = new CategorySyncService();
        $this->productSyncService = new ProductSyncService();
        $this->reviewSyncService = new ReviewSyncService();
        $this->orderService = new OrderService();
        $this->orderItemSyncService = new OrderItemSyncService();
    }
    public function dataSync($from, $post_type = 'product') : bool
    {
        try {
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once \ABSPATH . 'wp-admin/includes/file.php';
                \WP_Filesystem();
            }
            $storage_dir = \WP_CONTENT_DIR . '/uploads/reviewx';
            if (!$wp_filesystem->is_dir($storage_dir)) {
                $wp_filesystem->mkdir($storage_dir, 0777);
            }
            $post_type = sanitize_key($post_type);
            $file_name = $post_type === 'product' ? "shop-bulk-data.jsonl" : "{$post_type}-cpt-bulk-data.jsonl";
            $file_path = $storage_dir . '/' . $file_name;
            $buffer = "";
            $total_objects = 0;
            if ($post_type === 'product') {
                $total_objects += $this->userSyncService->syncUser($buffer);
                if (\class_exists('WooCommerce') || $this->dataSyncHandler->wc_data_exists_in_db()) {
                    $total_objects += $this->productSyncService->processProductForSync($buffer, $post_type);
                    $total_objects += $this->reviewSyncService->processReviewForSync($buffer, $post_type);
                    $total_objects += $this->orderItemSyncService->syncOrder($buffer);
                    $total_objects += $this->orderItemSyncService->syncOrderItem($buffer);
                }
            } else {
                $total_objects += $this->productSyncService->processProductForSync($buffer, $post_type);
                $total_objects += $this->reviewSyncService->processReviewForSync($buffer, $post_type);
            }
            $wp_filesystem->put_contents($file_path, $buffer, \FS_CHMOD_FILE);
            (new WebhookRequestApi())->finishedWebhook(['total_objects' => $total_objects, 'status' => 'finished', 'from' => $from, 'post_type' => $post_type, 'resource_url' => Helper::getRestAPIurl() . '/api/v1/synced/data?post_type=' . $post_type]);
            return \true;
        } catch (\Throwable $e) {
            return \false;
        }
    }
    protected function dataSyncFile($file_path, $from, $total_objects)
    {
        $file_info = $this->prepareFileInfo($file_path);
        $file = $_FILES['file'] = $file_info;
        $fileUpload = (new DataSyncApi())->dataSync($file, $from, $total_objects);
        global $wp_filesystem;
        if (!empty($wp_filesystem) && $wp_filesystem->exists($file_path)) {
            \wp_delete_file($file_path);
        } elseif (\file_exists($file_path)) {
            \wp_delete_file($file_path);
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
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once \ABSPATH . 'wp-admin/includes/file.php';
            \WP_Filesystem();
        }
        $storage_dir = \WP_CONTENT_DIR . '/uploads/reviewx';
        if (!$wp_filesystem->is_dir($storage_dir)) {
            $wp_filesystem->mkdir($storage_dir, 0777);
        }
        $file_path = $storage_dir . '/manual_sync.jsonl';
        $buffer = "";
        $totalLines = 0;
        if ("users" === $data['action']) {
            $totalLines = \get_option('rvx_sync_number');
            $totalLines += (new UserSyncService())->syncUser($buffer);
            \update_option('rvx_sync_number', $totalLines);
        }
        if ("categories" === $data['action']) {
            if (\class_exists('WooCommerce') || $this->dataSyncHandler->wc_data_exists_in_db()) {
                $processProduct = new ProductSyncService();
                $totalLines += $processProduct->processProductForSync($buffer, 'product');
                \update_option('rvx_sync_number', $totalLines);
            }
        }
        if ("reviews" === $data['action']) {
            if (\class_exists('WooCommerce') || $this->dataSyncHandler->wc_data_exists_in_db()) {
                $totalLines = \get_option('rvx_sync_number');
                $totalLines += (new ReviewSyncService())->processReviewForSync($buffer, 'product');
                \update_option('rvx_sync_number', $totalLines);
            }
        }
        if ("order" === $data['action']) {
            if (\class_exists('WooCommerce') || $this->dataSyncHandler->wc_data_exists_in_db()) {
                $order = new OrderItemSyncService();
                $totalLines = \get_option('rvx_sync_number');
                $totalLines += $order->syncOrder($buffer);
                $totalLines += $order->syncOrderItem($buffer);
                \update_option('rvx_sync_number', $totalLines);
            }
        }
        if (!empty($buffer)) {
            $current = $wp_filesystem->exists($file_path) ? $wp_filesystem->get_contents($file_path) : '';
            $wp_filesystem->put_contents($file_path, $current . $buffer, \FS_CHMOD_FILE);
        }
        if ("api" === $data['action']) {
            $totalLines = \get_option('rvx_sync_number');
            return $this->dataSyncFile($file_path, 'register', $totalLines);
        }
    }
}

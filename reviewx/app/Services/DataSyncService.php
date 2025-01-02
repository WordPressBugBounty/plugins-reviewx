<?php

namespace Rvx\Services;

use Rvx\Api\DataSyncApi;
use Exception;
class DataSyncService extends \Rvx\Services\Service
{
    protected \Rvx\Services\OrderService $orderService;
    public function __construct()
    {
        $this->orderService = new \Rvx\Services\OrderService();
    }
    public function dataSync($from)
    {
        try {
            $file_path = RVX_DIR_PATH . 'sync.jsonl';
            $file = \fopen($file_path, 'w');
            $totalLines = 0;
            $syncedCaterories = new \Rvx\Services\CategorySyncService();
            $totalLines += $syncedCaterories->syncCategory($file);
            $totalLines += (new \Rvx\Services\UserSyncService())->syncUser($file);
            $processProduct = new \Rvx\Services\ProductSyncService($syncedCaterories);
            $totalLines += $processProduct->processProductForSync($file);
            $totalLines += (new \Rvx\Services\ReviewSyncService())->processReviewForSync($file);
            if (\class_exists('WooCommerce')) {
                $order = new \Rvx\Services\OrderItemSyncService();
                $totalLines += $order->syncOrder($file);
                $totalLines += $order->syncOrderItem($file);
            }
            \fclose($file);
            $file_info = $this->prepareFileInfo($file_path);
            $file = $_FILES['file'] = $file_info;
            $fileUpload = (new DataSyncApi())->dataSync($file, $from, $totalLines);
            if (\file_exists($file_path)) {
                \unlink($file_path);
            }
            return $fileUpload;
        } catch (Exception $e) {
            throw new Exception("An error occurred");
        }
    }
    private function prepareFileInfo($file_path)
    {
        return ['name' => \basename($file_path), 'full_path' => \realpath($file_path), 'type' => "application/json", 'tmp_name' => $file_path, 'error' => 0, 'size' => \filesize($file_path)];
    }
    public function syncStatus()
    {
        return (new DataSyncApi())->syncStatus();
    }
}

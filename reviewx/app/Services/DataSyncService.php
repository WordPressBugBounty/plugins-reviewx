<?php

namespace Rvx\Services;

use Rvx\Api\DataSyncApi;
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
            $syncedCaterories = new \Rvx\Services\CategorySyncService();
            $catCount = $syncedCaterories->syncCategory($file);
            $userCount = (new \Rvx\Services\UserSyncService())->syncUser($file);
            $totalLines = $catCount + $userCount;
            if (\class_exists('WooCommerce')) {
                $processProduct = new \Rvx\Services\ProductSyncService($syncedCaterories);
                $productCount = $processProduct->processProductForSync($file);
                $orderCount = (new \Rvx\Services\OrderItemSyncService())->syncOrder($file);
                $orderItemCount = (new \Rvx\Services\OrderItemSyncService())->syncOrderItem($file);
                $reviewCount = (new \Rvx\Services\ReviewSyncService($processProduct))->processReviewForSync($file);
                $totalLines += $productCount + $orderCount + $orderItemCount + $reviewCount;
            }
            $file_info = $this->prepareFileInfo($file_path);
            $file = $_FILES['file'] = $file_info;
            $fileUpload = (new DataSyncApi())->dataSync($file, $from, $totalLines);
            if (\file_exists($file_path)) {
                \unlink($file_path);
            }
            return $fileUpload;
        } catch (\Exception $e) {
            throw new \Rvx\Services\Exception("An error occurred");
        }
    }
    private function prepareFileInfo($file_path)
    {
        return ['name' => \basename($file_path), 'full_path' => \realpath($file_path), 'type' => "application/json", 'tmp_name' => $file_path, 'error' => 0, 'size' => \filesize($file_path)];
    }
}

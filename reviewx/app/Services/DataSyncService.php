<?php

namespace Rvx\Services;

use Rvx\Api\DataSyncApi;
use Exception;
use Rvx\Api\WebhookRequestApi;
use Rvx\Utilities\Helper;
class DataSyncService extends \Rvx\Services\Service
{
    protected \Rvx\Services\OrderService $orderService;
    public function __construct()
    {
        $this->orderService = new \Rvx\Services\OrderService();
    }
    public function dataSync($from) : bool
    {
        try {
            \mkdir(WP_CONTENT_DIR . '/uploads/reviewx', 0777, \true);
            $file_path = WP_CONTENT_DIR . '/uploads/reviewx/shop-bulk-data.jsonl';
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
            /*$file_info = $this->prepareFileInfo($file_path);
              $file = $_FILES['file'] = $file_info;
              $fileUpload = (new DataSyncApi())->dataSync($file, $from, $totalLines);
              if (file_exists($file_path)) {
                 unlink($file_path);
              }*/
            (new WebhookRequestApi())->finishedWebhook(['total_objects' => $totalLines, 'status' => 'finished', 'from' => $from, 'resource_url' => home_url() . '/wp-json/reviewx/api/v1/synced/data']);
            return \true;
            //            return $fileUpload;
        } catch (Exception $e) {
            return \false;
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

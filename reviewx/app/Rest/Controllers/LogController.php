<?php

namespace Rvx\Rest\Controllers;

use Exception;
use Rvx\Services\CategorySyncService;
use Rvx\Services\DataSyncService;
use Rvx\Services\OrderItemSyncService;
use Rvx\Services\ProductSyncService;
use Rvx\Services\ReviewSyncService;
use Rvx\Services\UserSyncService;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\Services\CacheServices;
class LogController implements InvokableContract
{
    protected CacheServices $cacheServices;
    public function __construct()
    {
        $this->cacheServices = new CacheServices();
    }
    /**
     * @return void
     */
    public function __invoke()
    {
    }
    public function rvxRecentLog($request)
    {
        $data = $request->get_params();
        $this->directoryCreate($data);
        $this->logDownload($data);
        $this->deleteLog($data);
    }
    public function directoryCreate($data)
    {
        if ($data['action'] === 'dir') {
            $log_folder = RVX_DIR_PATH . 'log/';
            if (!\file_exists($log_folder) && !\mkdir($log_folder, 0755, \true) && !\is_dir($log_folder)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $log_folder));
            }
        }
    }
    public function logDownload($data)
    {
        if ($data['action'] === 'log') {
            $logPath = RVX_DIR_PATH . 'log/';
            $files = \glob($logPath . '*');
            if (empty($files)) {
                echo 'No log files found';
            }
            $recentFile = null;
            foreach ($files as $file) {
                if (\is_null($recentFile) || \filemtime($file) > \filemtime($recentFile)) {
                    $recentFile = $file;
                }
            }
            if (!\file_exists($recentFile)) {
                echo 'File not found';
            }
            \header('Content-Type: text/plain');
            \header('Content-Disposition: attachment; filename="' . \basename($recentFile) . '"');
            \header('Content-Length: ' . \filesize($recentFile));
            \ob_clean();
            \flush();
            \readfile($recentFile);
            exit;
        }
    }
    public function deleteLog($data)
    {
        if ($data['action'] === 'remove') {
            $log_folder = RVX_DIR_PATH . 'log/';
            if (!\is_dir($log_folder)) {
                echo 'Log folder does not exist';
            }
            $files = \glob($log_folder . '/*');
            foreach ($files as $file) {
                if (\is_file($file)) {
                    \unlink($file);
                }
            }
            if (\rmdir($log_folder)) {
                echo 'Log folder and files deleted successfully';
            } else {
                echo 'Failed to delete the log folder';
            }
        }
    }
    public function appendJsonSync($request)
    {
        $action = $request->get_param('action');
        $from = $request->get_param('from');
        if ($action === 'create_jsonl') {
            $this->createJsonl();
        }
        if ($action === 'download') {
            $this->downloadJsonl();
        }
        if ($action === 'manual_sync') {
            $dataResponse = (new DataSyncService())->dataSync($from);
            $this->cacheServices->removeCache();
            if (!$dataResponse) {
                return Helper::rvxApi(['error' => "Data Sync Failed"])->fails('Data Sync Failed', $dataResponse->getStatusCode());
            }
        }
        return Helper::rvxApi()->success('Data Synced Successfully');
    }
    public function createJsonl()
    {
        try {
            $file_path = RVX_DIR_PATH . 'sync.jsonl';
            $file = \fopen($file_path, 'w');
            $syncedCaterories = new CategorySyncService();
            $syncedCaterories->syncCategory($file);
            (new UserSyncService())->syncUser($file);
            $processProduct = new ProductSyncService($syncedCaterories);
            $processProduct->processProductForSync($file);
            (new ReviewSyncService($processProduct))->processReviewForSync($file);
            if (\class_exists('WooCommerce')) {
                $order = new OrderItemSyncService();
                $order->syncOrder($file);
                $order->syncOrderItem($file);
            }
            echo 'jsonl create done';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function downloadJsonl()
    {
        $syncJsonlDownload = RVX_DIR_PATH . 'sync.jsonl';
        if (\file_exists($syncJsonlDownload)) {
            \header('Content-Type: text/plain');
            \header('Content-Disposition: attachment; filename="' . \basename($syncJsonlDownload) . '"');
            \header('Content-Length: ' . \filesize($syncJsonlDownload));
            \header('Content-Length: ' . \filesize($syncJsonlDownload));
            \ob_clean();
            \flush();
            \readfile($syncJsonlDownload);
            \unlink($syncJsonlDownload);
            exit;
        }
    }
}

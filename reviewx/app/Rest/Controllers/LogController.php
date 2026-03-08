<?php

namespace Rvx\Rest\Controllers;

\defined("ABSPATH") || exit;
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
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once \ABSPATH . 'wp-admin/includes/file.php';
                \WP_Filesystem();
            }
            $log_folder = RVX_DIR_PATH . 'log/';
            if (!$wp_filesystem->exists($log_folder)) {
                if (!$wp_filesystem->mkdir($log_folder, 0755)) {
                    throw new \RuntimeException(\sprintf('Directory "%s" was not created', \esc_html($log_folder)));
                }
            }
        }
    }
    public function logDownload($data)
    {
        if ($data['action'] === 'log') {
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once \ABSPATH . 'wp-admin/includes/file.php';
                \WP_Filesystem();
            }
            $logPath = RVX_DIR_PATH . 'log/';
            $files = \glob($logPath . '*');
            // glob is generally acceptable if WP_Filesystem doesn't provide a direct equivalent that is as easy to use here, but we'll stick to contents if possible.
            if (empty($files)) {
                \esc_html_e('No log files found', 'reviewx');
                return;
            }
            $recentFile = null;
            foreach ($files as $file) {
                if (\is_null($recentFile) || $wp_filesystem->mtime($file) > $wp_filesystem->mtime($recentFile)) {
                    $recentFile = $file;
                }
            }
            if (!$wp_filesystem->exists($recentFile)) {
                \esc_html_e('File not found', 'reviewx');
                return;
            }
            \header('Content-Type: text/plain');
            \header('Content-Disposition: attachment; filename="' . \basename($recentFile) . '"');
            \header('Content-Length: ' . $wp_filesystem->size($recentFile));
            \ob_clean();
            \flush();
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $wp_filesystem->get_contents($recentFile);
            exit;
        }
    }
    public function deleteLog($data)
    {
        if ($data['action'] === 'remove') {
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once \ABSPATH . 'wp-admin/includes/file.php';
                \WP_Filesystem();
            }
            $log_folder = RVX_DIR_PATH . 'log/';
            if (!$wp_filesystem->is_dir($log_folder)) {
                \esc_html_e('Log folder does not exist', 'reviewx');
                return;
            }
            $files = $wp_filesystem->dirlist($log_folder);
            if ($files) {
                foreach ($files as $file) {
                    $wp_filesystem->delete($log_folder . $file['name']);
                }
            }
            if ($wp_filesystem->rmdir($log_folder)) {
                \esc_html_e('Log folder and files deleted successfully', 'reviewx');
            } else {
                \esc_html_e('Failed to delete the log folder', 'reviewx');
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
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once \ABSPATH . 'wp-admin/includes/file.php';
                \WP_Filesystem();
            }
            $file_buffer = "";
            $syncedCaterories = new CategorySyncService();
            $syncedCaterories->syncCategory($file_buffer);
            (new UserSyncService())->syncUser($file_buffer);
            $processProduct = new ProductSyncService();
            $processProduct->processProductForSync($file_buffer, 'product');
            (new ReviewSyncService())->processReviewForSync($file_buffer, 'product');
            if (\class_exists('WooCommerce')) {
                $order = new OrderItemSyncService();
                $order->syncOrder($file_buffer);
                $order->syncOrderItem($file_buffer);
            }
            $file_path = RVX_DIR_PATH . 'sync.jsonl';
            $wp_filesystem->put_contents($file_path, $file_buffer, \FS_CHMOD_FILE);
            \esc_html_e('jsonl create done', 'reviewx');
        } catch (Exception $e) {
            return \esc_html($e->getMessage());
        }
    }
    public function downloadJsonl()
    {
        $syncJsonlDownload = RVX_DIR_PATH . 'sync.jsonl';
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once \ABSPATH . 'wp-admin/includes/file.php';
            \WP_Filesystem();
        }
        if ($wp_filesystem->exists($syncJsonlDownload)) {
            \header('Content-Type: text/plain');
            \header('Content-Disposition: attachment; filename="' . \basename($syncJsonlDownload) . '"');
            \header('Content-Length: ' . $wp_filesystem->size($syncJsonlDownload));
            \ob_clean();
            \flush();
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $wp_filesystem->get_contents($syncJsonlDownload);
            $wp_filesystem->delete($syncJsonlDownload);
            exit;
        }
    }
}

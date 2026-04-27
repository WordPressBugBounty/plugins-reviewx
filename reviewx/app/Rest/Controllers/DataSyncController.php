<?php

namespace ReviewX\Rest\Controllers;

\defined("ABSPATH") || exit;
use WP_REST_Request;
use ReviewX\Models\Site;
use ReviewX\Services\CacheServices;
use ReviewX\Services\CptService;
use ReviewX\Services\DataSyncService;
use ReviewX\Services\SettingService;
use ReviewX\Utilities\Helper;
use Throwable;
class DataSyncController
{
    protected SettingService $settingService;
    protected DataSyncService $dataSyncService;
    protected CptService $cptService;
    protected CacheServices $cacheServices;
    public function __construct()
    {
        $this->dataSyncService = new DataSyncService();
        $this->settingService = new SettingService();
        $this->cptService = new CptService();
        $this->cacheServices = new CacheServices();
    }
    public function dataSync()
    {
        $resp = $this->dataSyncService->dataSync('default');
        if ($resp) {
            $this->cacheServices->refreshPendingReviewNoticeSummary();
            return Helper::rvxApi()->success('Data Sync Success');
        } else {
            return Helper::rvxApi()->fails('Data Sync Failed');
        }
    }
    public function dataSynComplete()
    {
        // Update all DB settings from API to WP DB
        $this->updateSettingsOnSync();
        $this->cacheServices->refreshPendingReviewNoticeSummary();
        return Site::where("is_saas_sync", 0)->update(['is_saas_sync' => 1]);
    }
    public function syncStatus()
    {
        \header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        \header("Pragma: no-cache");
        \header("Pragma: no-cache");
        $response = $this->dataSyncService->syncStatus();
        if (!empty($response->getApiData()['sync_stats']) && $response->getApiData()['sync_stats'] === 1) {
            // Update all DB settings from API to WP DB
            $this->updateSettingsOnSync();
            $this->cacheServices->refreshPendingReviewNoticeSummary();
            Site::where("is_saas_sync", 0)->update(['is_saas_sync' => 1]);
        }
        return Helper::saasResponse($response);
    }
    public function updateSettingsOnSync()
    {
        $local_cpt_settings = $this->getStoredCptSettings();
        $local_widget_settings = $this->settingService->getWidgetSettings();
        $local_review_settings = $this->getStoredReviewSettingsByPostType($local_cpt_settings);
        $remote_cpt_settings = $this->refreshCptSettingsFromSaas();
        $this->syncLocalCptSettingsToSaas($local_cpt_settings, $remote_cpt_settings);
        $remote_cpt_settings = $this->refreshCptSettingsFromSaas();
        $this->syncLocalCptStatusesToSaas($local_cpt_settings, $remote_cpt_settings);
        $remote_cpt_settings = $this->refreshCptSettingsFromSaas();
        $post_types = $this->resolveReviewSettingsPostTypes($local_review_settings, $remote_cpt_settings);
        foreach ($post_types as $post_type) {
            $this->restoreReviewSettingsAfterSync($post_type, $local_review_settings[$post_type] ?? []);
        }
        $this->restoreWidgetSettingsAfterSync($local_widget_settings);
    }
    public function dataManualSync($request)
    {
        try {
            $response = $this->dataSyncService->dataManualSync($request->get_params());
            if ($this->isSuccessfulApiResponse($response)) {
                $this->cacheServices->refreshPendingReviewNoticeSummary();
            }
            return Helper::saasResponse($response);
        } catch (Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('General settings saved failed', $e->getCode());
        }
    }
    public function syncedData(WP_REST_Request $request)
    {
        $post_type = $request->get_param('post_type') ?? 'product';
        // Sanitize just in case:
        $post_type = \sanitize_key($post_type);
        if ($post_type === 'product') {
            $file_name = "shop-bulk-data.jsonl";
        } else {
            $file_name = "{$post_type}-cpt-bulk-data.jsonl";
        }
        $file_path = \WP_CONTENT_DIR . '/uploads/reviewx/' . $file_name;
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once \ABSPATH . 'wp-admin/includes/file.php';
            \WP_Filesystem();
        }
        if (!$wp_filesystem->exists($file_path)) {
            return Helper::rvxApi()->fails('File not found for post_type: ' . $post_type, 404);
        }
        \header('Content-Description: File Transfer');
        \header('Content-Type: application/jsonl');
        \header('Content-Disposition: attachment; filename="' . \basename($file_path) . '"');
        \header('Expires: 0');
        \header('Cache-Control: must-revalidate');
        \header('Pragma: public');
        \header('Content-Length: ' . $wp_filesystem->size($file_path));
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $wp_filesystem->get_contents($file_path);
        exit;
    }
    private function getStoredCptSettings() : array
    {
        $cpt_settings = \get_option('_rvx_cpt_settings', []);
        return \is_array($cpt_settings) ? $cpt_settings : [];
    }
    private function getStoredReviewSettingsByPostType(array $local_cpt_settings) : array
    {
        $review_settings = ['product' => $this->settingService->getReviewSettings('product')];
        foreach ($this->mapCptEntriesByPostType($local_cpt_settings) as $post_type => $cpt_setting) {
            $review_settings[$post_type] = $this->settingService->getReviewSettings($post_type);
        }
        return $review_settings;
    }
    private function refreshCptSettingsFromSaas() : array
    {
        $response = (new \ReviewX\Rest\Controllers\CptController())->cptGetOnSync();
        $data = $response[1]['data'] ?? [];
        if (\is_array($data)) {
            return $data;
        }
        return $this->getStoredCptSettings();
    }
    private function syncLocalCptSettingsToSaas(array $local_cpt_settings, array $remote_cpt_settings) : void
    {
        $remote_cpt_map = $this->mapCptEntriesByPostType($remote_cpt_settings);
        foreach ($this->mapCptEntriesByPostType($local_cpt_settings) as $post_type => $local_cpt) {
            $configuration = $this->normalizeCptConfiguration($local_cpt['configuration'] ?? [], $post_type);
            if (!$this->hasMeaningfulCptConfiguration($configuration)) {
                continue;
            }
            try {
                if (isset($remote_cpt_map[$post_type]['uid'])) {
                    $this->cptService->cptUpdate(['uid' => $remote_cpt_map[$post_type]['uid'], 'post_type' => $post_type, 'configuration' => $configuration]);
                    continue;
                }
                $this->cptService->cptStore(['post_type' => $post_type, 'configuration' => $configuration]);
            } catch (Throwable $e) {
                continue;
            }
        }
    }
    private function syncLocalCptStatusesToSaas(array $local_cpt_settings, array $remote_cpt_settings) : void
    {
        $remote_cpt_map = $this->mapCptEntriesByPostType($remote_cpt_settings);
        foreach ($this->mapCptEntriesByPostType($local_cpt_settings) as $post_type => $local_cpt) {
            if (!isset($remote_cpt_map[$post_type]['uid'])) {
                continue;
            }
            $desired_status = $this->normalizeCptStatusValue($local_cpt['status'] ?? null);
            $current_status = $this->normalizeCptStatusValue($remote_cpt_map[$post_type]['status'] ?? null);
            if ($desired_status === null || $desired_status === $current_status) {
                continue;
            }
            try {
                $this->cptService->cptStatusChange(['uid' => $remote_cpt_map[$post_type]['uid'], 'status' => $desired_status]);
            } catch (Throwable $e) {
                continue;
            }
        }
    }
    private function resolveReviewSettingsPostTypes(array $local_review_settings, array $remote_cpt_settings) : array
    {
        $post_types = ['product' => 'product'];
        foreach (\array_keys($local_review_settings) as $post_type) {
            $post_types[$post_type] = $post_type;
        }
        foreach (\array_keys($this->mapCptEntriesByPostType($remote_cpt_settings)) as $post_type) {
            $post_types[$post_type] = $post_type;
        }
        return \array_values($post_types);
    }
    private function restoreReviewSettingsAfterSync(string $post_type, array $local_review_settings) : void
    {
        $review_response = (new \ReviewX\Rest\Controllers\SettingController())->getApiReviewSettingsOnSync($post_type);
        $remote_review_settings = $review_response['data']['review_settings'] ?? [];
        if ($this->settingService->hasMeaningfulReviewSettings($local_review_settings)) {
            $merged_review_settings = $this->settingService->mergeReviewSettingsForSync(\is_array($remote_review_settings) ? $remote_review_settings : [], $local_review_settings);
            $payload = $merged_review_settings['reviews'] ?? $merged_review_settings;
            $payload['post_type'] = $post_type;
            try {
                $response = $this->settingService->saveApiReviewSettings($payload);
                if ($this->isSuccessfulApiResponse($response)) {
                    $saved_review_settings = $response->getApiData()['review_settings'] ?? [];
                    $this->settingService->syncWooCommerceOptionsFromReviewSettings($saved_review_settings, $post_type);
                    $this->settingService->updateReviewSettingsOnSync($saved_review_settings, $post_type);
                    return;
                }
            } catch (Throwable $e) {
                return;
            }
            return;
        }
        if ($this->settingService->hasMeaningfulReviewSettings($remote_review_settings)) {
            $this->settingService->syncWooCommerceOptionsFromReviewSettings($remote_review_settings, $post_type);
            $this->settingService->updateReviewSettingsOnSync($remote_review_settings, $post_type);
        }
    }
    private function restoreWidgetSettingsAfterSync(array $local_widget_settings) : void
    {
        $widget_response = (new \ReviewX\Rest\Controllers\SettingController())->getApiWidgetSettingsOnSync();
        $remote_widget_settings = $widget_response['data']['widget_settings'] ?? [];
        if ($this->settingService->hasMeaningfulWidgetSettings($local_widget_settings)) {
            $merged_widget_settings = $this->settingService->mergeSettingsWithLocalPreference(\is_array($remote_widget_settings) ? $remote_widget_settings : [], $local_widget_settings);
            try {
                $response = $this->settingService->saveWidgetSettings($merged_widget_settings);
                if ($this->isSuccessfulApiResponse($response)) {
                    $saved_widget_settings = $response->getApiData()['widget_settings'] ?? [];
                    $this->settingService->updateWidgetSettings($saved_widget_settings);
                    return;
                }
            } catch (Throwable $e) {
                return;
            }
            return;
        }
        if ($this->settingService->hasMeaningfulWidgetSettings($remote_widget_settings)) {
            $this->settingService->updateWidgetSettings($remote_widget_settings);
        }
    }
    private function mapCptEntriesByPostType(array $cpt_settings) : array
    {
        $mapped_cpts = [];
        $cpt_reviews = $cpt_settings['reviews'] ?? [];
        if (!\is_array($cpt_reviews)) {
            return $mapped_cpts;
        }
        foreach ($cpt_reviews as $cpt_setting) {
            $post_type = \strtolower((string) ($cpt_setting['post_type'] ?? ''));
            if ($post_type === '' || $post_type === 'product') {
                continue;
            }
            $mapped_cpts[$post_type] = $cpt_setting;
        }
        return $mapped_cpts;
    }
    private function hasMeaningfulCptConfiguration(array $configuration) : bool
    {
        return !empty($configuration['post_type']) && !empty($configuration['layout_type']);
    }
    private function normalizeCptConfiguration($configuration, string $post_type) : array
    {
        $configuration = \is_array($configuration) ? $configuration : [];
        $configuration = $this->normalizeLegacyCptMulticriteria($configuration);
        if (empty($configuration['post_type'])) {
            $configuration['post_type'] = $post_type;
        }
        if (empty($configuration['post_type_name'])) {
            $configuration['post_type_name'] = \ucwords(\str_replace(['-', '_'], ' ', $post_type));
        }
        if (empty($configuration['layout_type'])) {
            $configuration['layout_type'] = 'grid';
        }
        return $configuration;
    }
    private function normalizeLegacyCptMulticriteria(array $configuration) : array
    {
        if (isset($configuration['multicriteria']) || !isset($configuration['multi_criteria_reviews']) || !\is_array($configuration['multi_criteria_reviews'])) {
            return $configuration;
        }
        $legacy_multicriteria = $configuration['multi_criteria_reviews'];
        $criteria_items = $legacy_multicriteria['criteria'] ?? $legacy_multicriteria['criterias'] ?? [];
        $criteria_keys = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'];
        $criterias = [];
        foreach ($criteria_items as $index => $criteria_item) {
            if (\is_array($criteria_item)) {
                $key = \sanitize_key($criteria_item['key'] ?? $criteria_keys[$index] ?? '');
                $name = \sanitize_text_field((string) ($criteria_item['name'] ?? ''));
            } else {
                $key = \sanitize_key($criteria_keys[$index] ?? '');
                $name = \sanitize_text_field((string) $criteria_item);
            }
            if ($key === '' || $name === '') {
                continue;
            }
            $criterias[] = ['key' => $key, 'name' => $name];
        }
        $configuration['multicriteria'] = ['enable' => !empty($legacy_multicriteria['enabled']) || !empty($legacy_multicriteria['enable']), 'criterias' => $criterias];
        return $configuration;
    }
    private function normalizeCptStatusValue($status) : ?int
    {
        if ($status === 1 || $status === '1' || $status === 'Enabled') {
            return 1;
        }
        if ($status === 0 || $status === '0' || $status === 'Disabled') {
            return 0;
        }
        return null;
    }
    private function isSuccessfulApiResponse($response) : bool
    {
        return \is_object($response) && \method_exists($response, 'getStatusCode') && $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    }
}

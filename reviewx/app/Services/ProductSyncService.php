<?php

namespace Rvx\Services;

use Exception;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
use Rvx\Services\CategorySyncService;
class ProductSyncService extends \Rvx\Services\Service
{
    protected $postMetaPriceRelation;
    protected $productCount = 0;
    protected $postMetaAverageRatingRelation;
    protected $postMetaRatingCountPercentageRelation;
    protected $postMetaReviewsCountRelation;
    protected $postMetaSalePriceRelation;
    protected $postMetaThumbnaiRelation;
    protected $postMetaAttachmentsRelation;
    protected $postMetaStarCountsRelation;
    protected $productids;
    protected $postAttachmentRelation;
    protected CategorySyncService $syncedCategories;
    protected $postTermRelation;
    public function __construct()
    {
        $this->syncedCategories = new CategorySyncService();
        $this->postTermRelation = $this->syncedCategories->getPostTermRelation();
    }
    public function processProductForSync($file, $post_type) : int
    {
        $this->syncProductsMeta($post_type);
        return $this->syncProducts($file, $post_type);
    }
    public function getProductAttachementRalation()
    {
        return $this->postAttachmentRelation;
    }
    public function syncProductsMeta($post_type)
    {
        // Base meta keys
        $dbTableKeys = ['rvx_avg_rating', 'rating', '_thumbnail_id', 'rvx_total_reviews', 'rvx_star_count_1', 'rvx_star_count_2', 'rvx_star_count_3', 'rvx_star_count_4', 'rvx_star_count_5'];
        // Add product-specific keys
        if ($post_type === 'product') {
            $dbTableKeys = \array_merge($dbTableKeys, ['_price', '_sale_price', '_wc_review_count', '_wc_average_rating', '_wc_rating_count']);
        }
        // Define relation targets by meta key
        $relationMap = ['_price' => 'postMetaPriceRelation', '_sale_price' => 'postMetaSalePriceRelation', '_wc_review_count' => 'postMetaReviewsCountRelation', '_wc_rating_count' => 'postMetaRatingCountPercentageRelation', '_thumbnail_id' => 'postMetaThumbnaiRelation', 'rvx_total_reviews' => 'postMetaReviewsCountRelation'];
        try {
            DB::table('postmeta')->whereIn('meta_key', $dbTableKeys)->chunk(100, function ($allPostMeta) use($relationMap) {
                foreach ($allPostMeta as $meta) {
                    $key = $meta->meta_key;
                    $pid = $meta->post_id;
                    $value = $meta->meta_value;
                    // Direct assignment using map
                    if (isset($relationMap[$key])) {
                        $this->{$relationMap[$key]}[$pid] = $value;
                        continue;
                    }
                    // Collect Star Counts for CPT
                    if (\strpos($key, 'rvx_star_count_') === 0) {
                        $starIndex = \str_replace('rvx_star_count_', '', $key);
                        // 1, 2, 3...
                        $this->postMetaStarCountsRelation[$pid][$starIndex] = $value;
                        continue;
                    }
                    // --- Rating Prioritization Logic ---
                    // Priority: _wc_average_rating > rvx_avg_rating > rating
                    if ($key === '_wc_average_rating' || $key === 'rvx_avg_rating' || $key === 'rating') {
                        // Assign only if not already assigned by a higher-priority field
                        if (!isset($this->postMetaAverageRatingRelation[$pid])) {
                            $this->postMetaAverageRatingRelation[$pid] = $value;
                        }
                    }
                }
            });
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function syncProducts($file, $post_type)
    {
        $productCount = 0;
        $attachmentRelation = [];
        $this->postMetaAttachmentsRelation = [];
        DB::table('posts')->select(['ID', 'post_type', 'post_title', 'post_name', 'post_excerpt', 'post_status', 'guid', 'post_modified', 'comment_count'])->orderBy('ID')->whereIn('post_type', [$post_type])->chunk(100, function ($products) use(&$attachmentRelation, &$file, &$productCount) {
            foreach ($products as $product) {
                $this->productids[] = $product->ID;
                $productImage = get_the_post_thumbnail_url($product->ID, 'full') ? get_the_post_thumbnail_url($product->ID, 'full') : null;
                if ($product->post_type !== 'product') {
                    $this->postMetaReviewsCountRelation[$product->ID] = $product->comment_count;
                }
                $formatedProduct = $this->processProduct($product, $productImage);
                if ($formatedProduct['post_type'] !== 'attachment') {
                    Helper::appendToJsonl($file, $formatedProduct);
                    $productCount++;
                }
            }
        });
        $this->setPostAttachemtRelation($attachmentRelation);
        Helper::rvxLog($productCount, "Product Done");
        return $productCount;
    }
    public function setPostAttachemtRelation($attachmentRelation) : void
    {
        $this->postAttachmentRelation = $attachmentRelation;
    }
    public function processProduct($product, $productImage) : array
    {
        $reviewsCount = isset($this->postMetaReviewsCountRelation[$product->ID]) ? (int) $this->postMetaReviewsCountRelation[$product->ID] : 0;
        $ratingCount = $this->postMetaRatingCountPercentageRelation[$product->ID] ?? [];
        // Handle CPT Star Counts (from rvx_ meta)
        if ($product->post_type !== 'product') {
            // Self-Healing: If star data is missing, calculate it NOW.
            if (!isset($this->postMetaStarCountsRelation[$product->ID])) {
                \Rvx\CPT\CptAverageRating::update_average_rating($product->ID);
                // Fetch fresh data immediately
                $freshStars = [];
                for ($i = 1; $i <= 5; $i++) {
                    $freshStars[$i] = (int) get_post_meta($product->ID, "rvx_star_count_{$i}", \true);
                }
                $this->postMetaStarCountsRelation[$product->ID] = $freshStars;
                $freshTotal = (int) get_post_meta($product->ID, 'rvx_total_reviews', \true);
                $this->postMetaReviewsCountRelation[$product->ID] = $freshTotal;
                $freshAvg = (float) get_post_meta($product->ID, 'rvx_avg_rating', \true);
                $this->postMetaAverageRatingRelation[$product->ID] = $freshAvg;
            }
            $cptStars = $this->postMetaStarCountsRelation[$product->ID];
            // Format to match what ratingCountsConverter expects or construct directly
            $ratingCounts = ["one" => (int) ($cptStars[1] ?? 0), "two" => (int) ($cptStars[2] ?? 0), "three" => (int) ($cptStars[3] ?? 0), "four" => (int) ($cptStars[4] ?? 0), "five" => (int) ($cptStars[5] ?? 0)];
            // Refresh total reviews count from relation if it was updated
            $reviewsCount = isset($this->postMetaReviewsCountRelation[$product->ID]) ? (int) $this->postMetaReviewsCountRelation[$product->ID] : $reviewsCount;
        } else {
            // Default WooCommerce logic
            // Ensure WooCommerce serialized rating count is converted to array
            if (\is_string($ratingCount)) {
                $decoded = @\unserialize($ratingCount);
                $ratingCount = \is_array($decoded) ? $decoded : [];
            }
            $ratingCounts = $this->ratingCountsConverter($ratingCount);
        }
        return ['rid' => 'rid://Product/' . (int) $product->ID, "post_type" => $product->post_type ?? null, "wp_id" => (int) ($product->ID ?? 0), "title" => isset($product->post_title) ? \htmlspecialchars($product->post_title, \ENT_QUOTES, 'UTF-8') : null, "url" => $product->guid ?? '', "description" => $product->post_excerpt ?? null, "price" => isset($this->postMetaPriceRelation[$product->ID]) ? Helper::formatToTwoDecimalPlaces($this->postMetaPriceRelation[$product->ID]) : 0, "discounted_price" => isset($this->postMetaSalePriceRelation[$product->ID]) ? Helper::formatToTwoDecimalPlaces($this->postMetaSalePriceRelation[$product->ID]) : 0, "slug" => $product->post_name ?? '', "status" => $this->productStatus($product->post_status ?? ''), "total_reviews" => $reviewsCount, "avg_rating" => isset($this->postMetaAverageRatingRelation[$product->ID]) ? Helper::formatToTwoDecimalPlaces($this->postMetaAverageRatingRelation[$product->ID]) : 0, "stars" => ["one" => $ratingCounts["one"], "two" => $ratingCounts["two"], "three" => $ratingCounts["three"], "four" => $ratingCounts["four"], "five" => $ratingCounts["five"]], "one_stars" => $ratingCounts["one"], "two_stars" => $ratingCounts["two"], "three_stars" => $ratingCounts["three"], "four_stars" => $ratingCounts["four"], "five_stars" => $ratingCounts["five"], "modified_date" => Helper::validateReturnDate($product->post_modified) ?? null, "image" => $productImage, "category_ids" => isset($this->postTermRelation[(int) $product->ID]) && \is_array($this->postTermRelation[(int) $product->ID]) ? \array_map('intval', $this->postTermRelation[(int) $product->ID]) : []];
    }
    private function ratingCountsConverter(array $ratingCount) : array
    {
        // Final output initialized to 0
        $stars = ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0];
        // Empty OR invalid input → return default immediately
        if (empty($ratingCount)) {
            return $stars;
        }
        foreach ($ratingCount as $rawKey => $value) {
            // Convert numeric strings
            $value = \is_numeric($value) ? (float) $value : 0;
            // Convert key to float (handles "3.5", 5, "4", etc.)
            $key = \is_numeric($rawKey) ? (float) $rawKey : null;
            if ($key === null) {
                continue;
            }
            // Round decimals down (3.5 => 3)
            $bucket = (int) \floor($key);
            switch ($bucket) {
                case 1:
                    $stars["one"] += $value;
                    break;
                case 2:
                    $stars["two"] += $value;
                    break;
                case 3:
                    $stars["three"] += $value;
                    break;
                case 4:
                    $stars["four"] += $value;
                    break;
                case 5:
                    $stars["five"] += $value;
                    break;
            }
        }
        return $stars;
    }
    public function productStatus($status) : int
    {
        switch ($status) {
            case 'publish':
                return 1;
            case 'private':
                return 2;
            default:
                return 3;
        }
    }
}

<?php

namespace Rvx\Services;

use Exception;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
class ProductSyncService extends \Rvx\Services\Service
{
    protected $postMetaPriceRelation;
    protected $allowedPostTypes;
    protected $productCount = 0;
    protected $postMetaAverageRatingRelation;
    protected $postMetaSalePriceRelation;
    protected $postMetaThumbnaiRelation;
    protected $postMetaAttachmentsRelation;
    protected $productids;
    protected $postAttachmentRelation;
    protected $syncedCategories;
    protected $postTermRelation;
    public function __construct(\Rvx\Services\CategorySyncService $syncedCategories)
    {
        $this->syncedCategories = $syncedCategories;
        $this->postTermRelation = $this->syncedCategories->getPostTermRelation();
        $this->allowedPostTypes = $this->getPublicPostType();
    }
    public function processProductForSync($file) : int
    {
        $this->syncPostMeta();
        return $this->syncPost($file);
    }
    public function getProductAttachementRalation()
    {
        return $this->postAttachmentRelation;
    }
    public function syncPostMeta()
    {
        try {
            DB::table('postmeta')->whereIn('meta_key', ['_price', '_sale_price', '_wc_average_rating', '_thumbnail_id'])->chunk(100, function ($allPostMeta) {
                foreach ($allPostMeta as $postMetas) {
                    if ($postMetas->meta_key === '_price') {
                        $this->postMetaPriceRelation[$postMetas->post_id] = $postMetas->meta_value;
                    }
                    if ($postMetas->meta_key === '_sale_price') {
                        $this->postMetaSalePriceRelation[$postMetas->post_id] = $postMetas->meta_value;
                    }
                    if ($postMetas->meta_key === '_wc_average_rating') {
                        $this->postMetaAverageRatingRelation[$postMetas->post_id] = $postMetas->meta_value;
                    }
                    if ($postMetas->meta_key === '_thumbnail_id') {
                        $this->postMetaThumbnaiRelation[$postMetas->post_id] = $postMetas->meta_value;
                    }
                }
            });
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function getPublicPostType() : array
    {
        $args = ['public' => \true, '_builtin' => \false];
        $customPostTypes = \array_values(get_post_types($args));
        return \array_merge($customPostTypes, ['attachment', 'post']);
    }
    public function syncPost($file)
    {
        $productCount = 0;
        $attachmentRelation = [];
        $this->postMetaAttachmentsRelation = [];
        DB::table('posts')->select(['ID', 'post_type', 'post_title', 'post_name', 'post_excerpt', 'post_status', 'post_modified'])->orderBy('ID')->whereIn('post_type', $this->allowedPostTypes)->chunk(100, function ($products) use(&$attachmentRelation, &$product_url_base, &$file, &$productCount) {
            foreach ($products as $product) {
                $product_url_base = $this->getCustomUrlBase($product->product_type);
                $this->productids[] = $product->ID;
                $productImage = get_the_post_thumbnail_url($product->ID, 'full') ? get_the_post_thumbnail_url($product->ID, 'full') : null;
                $formatedProduct = $this->processProduct($product, $product_url_base, $productImage);
                if ($product->post_type === 'attachment') {
                    $attachmentRelation[$product->ID] = $product->guid;
                    $this->postMetaAttachmentsRelation[$product->ID] = $product->guid;
                }
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
    public function getCustomUrlBase($post_type) : string
    {
        // Handle WooCommerce product URL base
        if (\class_exists('WooCommerce') && $post_type === 'product') {
            $permalinks = maybe_unserialize(get_option('woocommerce_permalinks'));
            $base_url = isset($permalinks['product_base']) ? $permalinks['product_base'] : 'product';
        } else {
            // Check if the post type is registered
            $post_type_object = get_post_type_object($post_type);
            if ($post_type_object && $post_type_object->public) {
                // Use the permalink structure if available
                $base_url = $post_type_object->rewrite['slug'] ?? $post_type;
                // Handle cases where the post type URL structure is customized or removed
                if ($post_type_object->rewrite && isset($post_type_object->rewrite['with_front']) && !$post_type_object->rewrite['with_front']) {
                    $base_url = \str_replace(trailingslashit(get_option('permalink_structure')), '', $base_url);
                }
            } else {
                // Default to the provided post type if unregistered or non-public
                $base_url = $post_type;
            }
        }
        // Construct the final URL
        $custom_base_url = home_url('/') . \trim($base_url, '/');
        return trailingslashit($custom_base_url);
    }
    public function processProduct($product, $product_url_base, $productImage) : array
    {
        return ['rid' => 'rid://Product/' . (int) $product->ID, "post_type" => $product->post_type ?? null, "wp_id" => (int) ($product->ID ?? 0), "title" => isset($product->post_title) ? \htmlspecialchars($product->post_title, \ENT_QUOTES, 'UTF-8') : null, "url" => $product_url_base . ($product->post_name ?? ''), "description" => $product->post_excerpt ?? null, "price" => isset($this->postMetaPriceRelation[$product->ID]) ? $this->postMetaPriceRelation[$product->ID] : 0, "discounted_price" => isset($this->postMetaSalePriceRelation[$product->ID]) ? $this->postMetaSalePriceRelation[$product->ID] : 0, "slug" => $product->post_name ?? '', "status" => $this->productStatus($product->post_status ?? ''), "total_reviews" => 0, "avg_rating" => isset($this->postMetaAverageRatingRelation[$product->ID]) ? $this->postMetaAverageRatingRelation[$product->ID] : 0, "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "modified_date" => Helper::validateReturnDate($product->post_modified) ?? null, "image" => $productImage, "category_ids" => isset($this->postTermRelation[(int) $product->ID]) && \is_array($this->postTermRelation[(int) $product->ID]) ? \array_map('intval', $this->postTermRelation[(int) $product->ID]) : []];
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

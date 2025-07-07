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
    protected $postMetaSalePriceRelation;
    protected $postMetaThumbnaiRelation;
    protected $postMetaAttachmentsRelation;
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
        $this->syncProductsMeta();
        return $this->syncProducts($file, $post_type);
    }
    public function getProductAttachementRalation()
    {
        return $this->postAttachmentRelation;
    }
    public function syncProductsMeta()
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
    public function syncProducts($file, $post_type)
    {
        $productCount = 0;
        $attachmentRelation = [];
        $this->postMetaAttachmentsRelation = [];
        DB::table('posts')->select(['ID', 'post_type', 'post_title', 'post_name', 'post_excerpt', 'post_status', 'guid', 'post_modified'])->orderBy('ID')->whereIn('post_type', [$post_type])->chunk(100, function ($products) use(&$attachmentRelation, &$file, &$productCount) {
            foreach ($products as $product) {
                $this->productids[] = $product->ID;
                $productImage = get_the_post_thumbnail_url($product->ID, 'full') ? get_the_post_thumbnail_url($product->ID, 'full') : null;
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
        return ['rid' => 'rid://Product/' . (int) $product->ID, "post_type" => $product->post_type ?? null, "wp_id" => (int) ($product->ID ?? 0), "title" => isset($product->post_title) ? \htmlspecialchars($product->post_title, \ENT_QUOTES, 'UTF-8') : null, "url" => $product->guid ?? '', "description" => $product->post_excerpt ?? null, "price" => isset($this->postMetaPriceRelation[$product->ID]) ? Helper::formatToTwoDecimalPlaces($this->postMetaPriceRelation[$product->ID]) : 0, "discounted_price" => isset($this->postMetaSalePriceRelation[$product->ID]) ? Helper::formatToTwoDecimalPlaces($this->postMetaSalePriceRelation[$product->ID]) : 0, "slug" => $product->post_name ?? '', "status" => $this->productStatus($product->post_status ?? ''), "total_reviews" => 0, "avg_rating" => isset($this->postMetaAverageRatingRelation[$product->ID]) ? Helper::formatToTwoDecimalPlaces($this->postMetaAverageRatingRelation[$product->ID]) : 0, "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "modified_date" => Helper::validateReturnDate($product->post_modified) ?? null, "image" => $productImage, "category_ids" => isset($this->postTermRelation[(int) $product->ID]) && \is_array($this->postTermRelation[(int) $product->ID]) ? \array_map('intval', $this->postTermRelation[(int) $product->ID]) : []];
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

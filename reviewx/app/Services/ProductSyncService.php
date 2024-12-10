<?php

namespace Rvx\Services;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\DB;
class ProductSyncService extends \Rvx\Services\Service
{
    protected $postMetaPriceRelation;
    protected $allowedPostTypes;
    protected $productCount = 0;
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
        $this->allowedPostTypes = $this->getPrivatePostType();
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
    public function syncPostMeta() : void
    {
        DB::table('postmeta')->chunk(100, function ($allPostMeta) {
            foreach ($allPostMeta as $postMetas) {
                if ($postMetas->meta_key === '_price') {
                    $this->postMetaPriceRelation[$postMetas->post_id] = $postMetas->meta_value;
                }
                if ($postMetas->meta_key === '_sale_price') {
                    $this->postMetaSalePriceRelation[$postMetas->post_id] = $postMetas->meta_value;
                }
                if ($postMetas->meta_key === '_thumbnail_id') {
                    $this->postMetaThumbnaiRelation[$postMetas->post_id] = $postMetas->meta_value;
                }
            }
        });
    }
    public function getPrivatePostType() : array
    {
        $args = ['public' => \true, '_builtin' => \false];
        $customPostTypes = \array_values(get_post_types($args));
        return \array_merge($customPostTypes, ['attachment', 'post']);
    }
    public function syncPost($file)
    {
        $productCount = 0;
        $product_url_base = $this->getcustomProductUrlBase();
        $attachmentRelation = [];
        $this->postMetaAttachmentsRelation = [];
        DB::table('posts')->orderBy('ID')->whereIn('post_type', $this->allowedPostTypes)->chunk(100, function ($products) use(&$attachmentRelation, &$product_url_base, &$file, &$productCount) {
            foreach ($products as $product) {
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
        return $productCount;
    }
    public function setPostAttachemtRelation($attachmentRelation) : void
    {
        $this->postAttachmentRelation = $attachmentRelation;
    }
    public function getcustomProductUrlBase() : string
    {
        // Fetch site URL and check pretty permalinks
        $permalinks = maybe_unserialize(get_option('woocommerce_permalinks'));
        $product_base = isset($permalinks['product_base']) ? $permalinks['product_base'] : '';
        return home_url() . '/' . \trim($product_base, '/') . '/';
    }
    public function processProduct($product, $product_url_base, $productImage) : array
    {
        return ['rid' => 'rid://Product/' . $product->ID, "post_type" => $product->post_type, "wp_id" => (int) $product->ID, "title" => $product->post_title, "url" => \urldecode($product_url_base . $product->post_name), "description" => \strip_tags($product->post_content), "price" => (float) $this->postMetaPriceRelation[$product->ID] ?? 0.0, "discounted_price" => (float) $this->postMetaSalePriceRelation[$product->ID] ?? 0.0, "slug" => \urldecode($product->post_name), "status" => $this->productStatus($product->post_status), "total_reviews" => 0, "avg_rating" => 0.0, "stars" => ["one" => 0, "two" => 0, "three" => 0, "four" => 0, "five" => 0], "one_stars" => 0, "two_stars" => 0, "three_stars" => 0, "four_stars" => 0, "five_stars" => 0, "modified_date" => $product->post_modified ?? null, "image" => $productImage ?? null, "category_ids" => $this->postTermRelation[(int) $product->ID] ?? []];
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

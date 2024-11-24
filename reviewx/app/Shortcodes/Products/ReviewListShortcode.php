<?php

namespace Rvx\Shortcodes\Products;

use Rvx\WPDrill\Facades\View;
use Rvx\WPDrill\Contracts\ShortcodeContract;
class ReviewListShortcode implements ShortcodeContract
{
    public function render(array $attrs, string $content = null) : string
    {
        $attrs = shortcode_atts(['title' => null, 'product_id' => null], $attrs);
        $productIds = $attrs['product_id'];
        $productIdArray = [];
        // If product_id is not null, process it
        if ($productIds) {
            // Split product_id by commas and trim whitespace
            $productIdArray = \array_map('trim', \explode(',', $productIds));
        }
        // Send multiple product IDs to the JavaScript variable
        $attributes = $this->productWiseReviewShow($productIdArray);
        // Return the view (empty or simplified, as no review data is required)
        return View::render('shortcode/reviewList', ['title' => $attrs['title'], 'data' => $attributes]);
    }
    public function productWiseReviewShow($productIds)
    {
        $productIdsJson = \json_encode($productIds);
        return $productIdsJson;
        /**
                echo '<script>
                        window.__rvx_attributes__ = {
                            ...window.__rvx_attributes__,
                            shortCodes: 
                            {
                                rvx_review_list: {
                                    1: ' . $productIdsJson . '
                                }
                            }
                        }
            </script>';
        */
        /**
            // Structure the data for the JavaScript window object
            echo '
            <script>
        
            document.addEventListener("DOMContentLoaded", function () {
                const alpineComponent = document.querySelector(\'[x-data="__reviewXState__()"]\');
                if (alpineComponent) {
                    const alpineData = Alpine.$data(alpineComponent);
                    console.log(alpineData);
                    if (alpineData) {
                        alpineData.rvxAttributes.shortCodes.rvx_review_list = ' . json_encode($productIds) . ';
                    }
                }
            });
            </script>
            ';
        */
    }
}

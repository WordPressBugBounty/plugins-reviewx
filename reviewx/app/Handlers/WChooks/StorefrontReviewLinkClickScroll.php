<?php

namespace Rvx\Handlers\WChooks;

use Rvx\CPT\CptHelper;
class StorefrontReviewLinkClickScroll
{
    protected $cptHelper;
    public function __construct()
    {
        $this->cptHelper = new CptHelper();
    }
    public function addScrollScript()
    {
        global $post;
        // Check if $post is available before accessing properties
        if (!isset($post) || !\is_object($post) && !is_admin()) {
            return;
        }
        // Define the target post types
        $enabled_post_types = $this->cptHelper->enabledCPT();
        $post_type = $post->post_type ?? '';
        if (!isset($enabled_post_types[$post_type])) {
            return;
        }
        // Check if WooCommerce is active and we are on a product page
        if (\function_exists('Rvx\\wc_get_product') && 'product' === $post_type) {
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('.woocommerce-review-link').forEach(function (link) {
                        link.addEventListener('click', function (e) {
                            e.preventDefault(); // Prevent default link behavior
    
                            // Scroll to the reviews tab
                            const reviewsTab = document.getElementById('tab-title-reviews');
                            if (reviewsTab) {
                                reviewsTab.scrollIntoView({ behavior: 'smooth', block: 'start' }); // Smooth scroll
    
                                // Open the tab if not already active
                                if (!reviewsTab.classList.contains('active')) {
                                    const tabLink = reviewsTab.querySelector('a');
                                    if (tabLink) {
                                        tabLink.click(); // Trigger the tab opening
                                    }
                                }
                            }
                        });
                    });
                });
            </script>
            <?php 
        }
        if ('product' !== $post_type) {
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('.comments-link').forEach(function (link) {
                        link.addEventListener('click', function (e) {
                            e.preventDefault(); // Prevent default link behavior
    
                            // Scroll to the reviews tab
                            const reviewsTab = document.getElementById('rvx-storefront-widget--aggregation__summary');
                            if (reviewsTab) {
                                reviewsTab.scrollIntoView({ behavior: 'smooth', block: 'start' }); // Smooth scroll
    
                                // Open the tab if not already active
                                if (!reviewsTab.classList.contains('active')) {
                                    const tabLink = reviewsTab.querySelector('a');
                                    if (tabLink) {
                                        tabLink.click(); // Trigger the tab opening
                                    }
                                }
                            }
                        });
                    });
                });
            </script>
            <?php 
        }
    }
}

<?php

namespace Rvx\Handlers\WChooks;

class StorefrontReviewLinkClickScroll
{
    public function addScrollScript()
    {
        global $post;
        // Check if WooCommerce is active and we are on a product page
        if (\function_exists('Rvx\\wc_get_product') && isset($post) && 'product' === $post->post_type) {
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
    }
}

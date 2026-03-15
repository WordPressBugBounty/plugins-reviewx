<?php

namespace ReviewX;

\defined('ABSPATH') || exit;
use ReviewX\Handlers\Customize\WCUserDashboardAddReview;
?>

<!-- Review Modal -->
<div id="reviewxForm" class="hidden">
    <div id="show-elem">
        <button id="back-prev-elem">Go Back</button>
        <div id="reviewx-order-form">
            <div class="hidden">
                <p><strong>Order ID:</strong> <span id="reviewx-order-id-display"></span></p>
                <p><strong>Product ID:</strong> <span id="reviewx-product-id-display"></span></p>
                <p><strong>Review ID:</strong> <span id="reviewx-review-id-display"></span></p>
                <p id="reviewx-product-image-display"></p>
                <strong>Product:</strong> <span id="reviewx-product-name-display"></span>
            </div>
            <?php 
//Load the review form
$reviewx_review_form = new WCUserDashboardAddReview();
$reviewx_review_form->renderRvxReviewForm();
?>
        </div>
    </div>
</div>

<style>
    .hidden {
        display: none;
    }
    .visible {
        display: block;
    }
</style><?php 

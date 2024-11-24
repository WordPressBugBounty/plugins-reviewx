<?php

namespace Rvx;

use Rvx\Handlers\Customize\WCUserDashboardAddReview;
?>

<!-- Review Modal -->
<div id="reviewxForm" class="hidden">
    <div id="show-elem">
        <button id="back-prev-elem">Go Back</button>
        <div id="rvx-order-form">
            <div class="hidden">
                <p><strong>Order ID:</strong> <span id="rvx-order-id-display"></span></p>
                <p><strong>Product ID:</strong> <span id="rvx-product-id-display"></span></p>
                <p><strong>Review ID:</strong> <span id="rvx-review-id-display"></span></p>
                <p id="rvx-product-image-display"></p>
                <strong>Product:</strong> <span id="rvx-product-name-display"></span>
            </div>
            <?php 
//Load the review form
$reviewForm = new WCUserDashboardAddReview();
$reviewForm->renderRvxReviewForm();
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

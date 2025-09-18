<?php

namespace Rvx;

\defined('ABSPATH') || exit;
use Rvx\Utilities\Auth\Client;
use Rvx\Utilities\Helper;
use Rvx\Services\SettingService;
do_action('woocommerce_before_account_orders', $has_orders);
?>

<?php 
if ($has_orders) {
    ?>
    <div>
        <table id="isShowTable"
               class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
            <thead>
            <tr>
                <?php 
    foreach (wc_get_account_orders_columns() as $column_id => $column_name) {
        ?>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php 
        echo esc_attr($column_id);
        ?>">
                        <span class="nobr"><?php 
        echo esc_html($column_name);
        ?></span>
                    </th>
                <?php 
    }
    ?>
                <?php 
    if (Client::getSync()) {
        ?>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-product-image">
                        <span class="nobr"><?php 
        esc_html_e('Image', 'reviewx');
        ?></span>
                    </th>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-review">
                        <span class="nobr"><?php 
        esc_html_e('Reviews', 'reviewx');
        ?></span>
                    </th>
                <?php 
    }
    ?>
            </tr>
            </thead>

            <tbody>
            <?php 
    foreach ($customer_orders->orders as $customer_order) {
        $order = wc_get_order($customer_order);
        $items = $order->get_items();
        foreach ($items as $item_id => $item) {
            $product_id = $item->get_product_id();
            $product = wc_get_product($product_id);
            // Initialize variables to prevent issues
            $product_image_id = 0;
            $product_image = '';
            // Check if $product is valid and method exists
            if ($product instanceof WC_Product && \method_exists($product, 'get_image_id')) {
                $product_image_id = $product->get_image_id();
            }
            // Get product image if ID exists
            if ($product_image_id) {
                $product_image = wp_get_attachment_image($product_image_id, [60, 60]);
                // Custom width and height
            }
            ?>
                    <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php 
            echo esc_attr($order->get_status());
            ?> order">
                        <?php 
            foreach (wc_get_account_orders_columns() as $column_id => $column_name) {
                ?>
                            <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php 
                echo esc_attr($column_id);
                ?>"
                                data-title="<?php 
                echo esc_attr($column_name);
                ?>">
                                <?php 
                if (has_action('woocommerce_my_account_my_orders_column_' . $column_id)) {
                    ?>
                                    <?php 
                    do_action('woocommerce_my_account_my_orders_column_' . $column_id, $order, $item);
                    ?>
                                <?php 
                } elseif ('order-number' === $column_id) {
                    ?>
                                    <a href="<?php 
                    echo esc_url($order->get_view_order_url());
                    ?>">
                                        <?php 
                    echo esc_html(_x('#', 'hash before order number', 'woocommerce') . $order->get_order_number());
                    ?>
                                    </a>
                                <?php 
                } elseif ('order-date' === $column_id) {
                    ?>
                                    <time datetime="<?php 
                    echo $order->get_date_created() ? esc_attr($order->get_date_created()->date('c')) : '';
                    ?>">
                                        <?php 
                    echo $order->get_date_created() ? esc_html(wc_format_datetime($order->get_date_created())) : 'N/A';
                    ?>
                                    </time>
                                <?php 
                } elseif ('order-status' === $column_id) {
                    ?>
                                    <?php 
                    echo esc_html(wc_get_order_status_name($order->get_status()));
                    ?>
                                <?php 
                } elseif ('order-total' === $column_id) {
                    ?>
                                    <?php 
                    echo wp_kses_post(\sprintf('%1$s for %2$s', $item->get_total(), $item->get_name()));
                    ?>
                                <?php 
                } elseif ('order-actions' === $column_id) {
                    ?>
                                    <?php 
                    $actions = wc_get_account_orders_actions($order);
                    if (!empty($actions)) {
                        foreach ($actions as $key => $action) {
                            echo '<a href="' . esc_url($action['url']) . '" class="woocommerce-button button ' . sanitize_html_class($key) . '">' . esc_html($action['name']) . '</a>';
                        }
                    }
                    ?>
                                <?php 
                }
                ?>
                            </td>
                        <?php 
            }
            ?>
                        <?php 
            if (Client::getSync()) {
                ?>
                            <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-product-image"
                                data-title="<?php 
                esc_attr_e('Product Image', 'reviewx');
                ?>">
                                <?php 
                echo wp_kses_post($product_image);
                ?>
                            </td>
                            <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-review"
                                data-title="<?php 
                esc_attr_e('Details', 'reviewx');
                ?>">
                                <?php 
                $review_id = Helper::retrieveReviewId($order->get_id(), $product_id, get_current_user_id());
                $review_id = !empty($review_id);
                $saas_order_status = (new SettingService())->getReviewSettings('product')['reviews']['review_eligibility'];
                $order_current_status = \str_replace(' ', '_', \strtolower(wc_get_order_status_name($order->get_status())));
                if ($order_current_status === 'completed') {
                    $order_current_status = 'completed_payment';
                } elseif ($order_current_status === 'pending') {
                    $order_current_status = 'pending_payment';
                }
                if (\is_array($saas_order_status) && \array_key_exists($order_current_status, $saas_order_status) && $saas_order_status[$order_current_status] === \true) {
                    ?>
                                    <a href="#reviewxForm" class="woocommerce-button button rvx-elem"
                                    data-order-id="<?php 
                    echo esc_attr($order->get_id());
                    ?>"
                                    data-product-id="<?php 
                    echo esc_attr($product_id);
                    ?>"
                                    data-item-id="<?php 
                    echo esc_attr($item_id);
                    ?>"
                                    data-product-name="<?php 
                    echo esc_attr($product->get_name());
                    ?>"
                                    data-product-image='<?php 
                    echo wp_kses_post($product_image);
                    ?>'
                                    data-review-id='<?php 
                    echo esc_attr($review_id);
                    ?>'>
                                        <?php 
                    esc_html_e('Add Review', 'reviewx');
                    ?>
                                    </a>
                                    <?php 
                }
                ?>
                            </td>
                        <?php 
            }
            ?>
                    </tr>
                    <?php 
        }
    }
    ?>
            </tbody>
        </table>

        <?php 
    include 'review-modal.php';
    ?>

    </div>

    <?php 
    do_action('woocommerce_before_account_orders_pagination');
    ?>

    <?php 
    if (1 < $customer_orders->max_num_pages) {
        ?>
        <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
            <?php 
        if (1 !== $current_page) {
            ?>
                <a class="woocommerce-button woocommerce-button--previous button"
                   href="<?php 
            echo esc_url(wc_get_endpoint_url('orders', $current_page - 1));
            ?>"><?php 
            esc_html_e('Previous', 'woocommerce');
            ?></a>
            <?php 
        }
        ?>

            <?php 
        if (\intval($customer_orders->max_num_pages) !== $current_page) {
            ?>
                <a class="woocommerce-button woocommerce-button--next button"
                   href="<?php 
            echo esc_url(wc_get_endpoint_url('orders', $current_page + 1));
            ?>"><?php 
            esc_html_e('Next', 'woocommerce');
            ?></a>
            <?php 
        }
        ?>
        </div>
    <?php 
    }
    ?>

<?php 
} else {
    ?>

    <?php 
    wc_print_notice(esc_html__('No order has been made yet.', 'woocommerce'), 'notice');
    ?>

<?php 
}
?>

<?php 
do_action('woocommerce_after_account_orders', $has_orders);
?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const rvxElems = document.querySelectorAll(".rvx-elem");
        rvxElems.forEach(function (rvxElem) {
            rvxElem.addEventListener("click", function (event) {
                event.preventDefault();
                const orderId = rvxElem.getAttribute('data-order-id');
                const productId = rvxElem.getAttribute('data-product-id');
                const itemId = rvxElem.getAttribute('data-item-id');
                const productName = rvxElem.getAttribute('data-product-name');
                const productImageHtml = rvxElem.getAttribute('data-product-image');
                const reviewId = rvxElem.getAttribute('data-review-id');

                // Set product and order details in the modal
                document.getElementById('rvx-order-id-display').textContent = orderId;
                document.getElementById('rvx-product-id-display').textContent = productId;
                document.getElementById('rvx-product-name-display').textContent = productName;
                document.getElementById('rvx-product-image-display').innerHTML = productImageHtml;
                document.getElementById('rvx-review-id-display').innerHTML = reviewId;

                // Show the product/order details
                document.getElementById('rvx-order-form').classList.remove('hidden');

                // Show the modal and "Go Back" button
                document.getElementById("reviewxForm").classList.remove("hidden");
                document.querySelector("#isShowTable").classList.add("hidden");
                
                const alpineComponent = document.querySelector('[x-data="__reviewXState__()"]');
                if (alpineComponent) {
                    const alpineData = Alpine.$data(alpineComponent);
                    if (alpineData) {
                        alpineData.initializeMyAccountReviewFormOnProductChange(productId);
                    }
                }

            });
        });

        // Handle "Go Back" button click
        const backToPrev = document.getElementById("back-prev-elem");
        backToPrev.addEventListener("click", function (event) {
            event.preventDefault();
            // Hide the product/order details
            document.getElementById("reviewxForm").classList.add("hidden");
            document.querySelector("#isShowTable").classList.remove("hidden");
        });
    });
</script><?php 

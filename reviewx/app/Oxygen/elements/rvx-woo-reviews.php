<?php

namespace Rvx\Oxygen\WooElements;

use Rvx\Builder\Support\OxygenBuilder;
use Rvx\Oxygen\RvxOxyWooEl;
if (!\class_exists('WooCommerce')) {
    return;
}
if (!\class_exists('Rvx\\OxyWooCommerce')) {
    return;
}
class RvxWooReviews extends RvxOxyWooEl
{
    function name()
    {
        return 'ReviewX Woo Reviews';
    }
    function woo_button_place()
    {
        return "single";
    }
    function icon()
    {
        return plugin_dir_url(__FILE__) . 'assets/' . \basename(__FILE__, '.php') . '.svg';
    }
    function wooTemplate($options)
    {
        global $product;
        $product = wc_get_product();
        setup_postdata($product->get_id());
        add_filter('rx_load_oxygen_style_controller', function ($data) use($options) {
            return $options;
        });
        return \call_user_func('comments_template', 'reviews');
    }
    function controls()
    {
        $reviewx_graph_criteria = $this->addControlSection("reviewx_graph_criteria", __("Review Summary", 'reviewx'), "assets/icon.png", $this);
        $reviewx_item = $this->addControlSection("reviewx_item", __("Review Card", 'reviewx'), "assets/icon.png", $this);
        $reviewx_filter = $this->addControlSection("reviewx_filter", __("Filter Options", 'reviewx'), "assets/icon.png", $this);
        $reviewx_form = $this->addControlSection("reviewx_form", __("Review Form", 'reviewx'), "assets/icon.png", $this);
        $reviewx_graph_criteria->addStyleControls((new OxygenBuilder())->prepareClasses('graph'));
        $reviewx_item->addStyleControls((new OxygenBuilder())->prepareClasses('reviewItem'));
        $reviewx_item->addStyleControl(array('selector' => '.rvx-review-card .rvx-review-card__body .rvx-review-user__avatar,.woocommerce-Tabs-panel #rvx-review-details .rvx-review-user__avatar', 'name' => __('Avatar Width', 'textdomain'), 'property' => 'width', 'control_type' => 'slider-measurebox'))->setRange('50', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_item->addStyleControl(array('selector' => '.rvx-review-card .rvx-review-card__body .rvx-review-user__avatar,.woocommerce-Tabs-panel #rvx-review-details .rvx-review-user__avatar', 'name' => __('Avatar Height', 'textdomain'), 'property' => 'height', 'control_type' => 'slider-measurebox'))->setRange('50', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_item->addStyleControl(array('selector' => '.rvx-review-card .rvx-review-card__body .rvx-review-user__avatar .rvx-review-user__avatar-fallback span,rvx-review-user__avatar .rvx-review-user__avatar-fallback span,
        .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__title,#rvx-review-details .rvx-review-info .rvx-review-info__title,
        .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__date,
        #rvx-storefront-widget .rvx-review-card .rvx-review-footer .rvx-review-footer__text,#rvx-storefront-widget #rvx-review-details .rvx-review-footer .rvx-review-footer__text,
        .rvx-review-card .rvx-review-card__body .rvx-review-user .rvx-review-user__name, #rvx-review-details .rvx-review-user .rvx-review-user__name,
        .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__feedback,#rvx-review-details .rvx-review-info .rvx-review-info__feedback, #rvx-review-details .rvx-review-info__date, #rvx-review-details .rvx-review-form__multicriteria--name', 'name' => __('Font Size', 'textdomain'), 'property' => 'font-size', 'control_type' => 'slider-measurebox'))->setRange('16', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_form->addStyleControls((new OxygenBuilder())->prepareClasses('form'));
        $reviewx_form->addStyleControl(array('selector' => '.rvx-review-card .rvx-review-card__body .rvx-review-user__avatar .rvx-review-user__avatar-fallback span,rvx-review-user__avatar,
                .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__title,#rvx-review-details .rvx-review-info .rvx-review-info__title,
                .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__date,
                #rvx-storefront-widget .rvx-review-card .rvx-review-footer .rvx-review-footer__text,#rvx-storefront-widget #rvx-review-details .rvx-review-footer .rvx-review-footer__text,
                .rvx-review-card .rvx-review-card__body .rvx-review-user .rvx-review-user__name, #rvx-review-details .rvx-review-user .rvx-review-user__name,
                .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__feedback,#rvx-review-details .rvx-review-info .rvx-review-info__feedback, #rvx-review-details .rvx-review-info__date, #rvx-review-details .rvx-review-form__multicriteria--name', 'name' => __('Font Size', 'textdomain'), 'property' => 'font-size', 'control_type' => 'slider-measurebox'))->setRange('16', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_form->addStyleControl(array('selector' => '#rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__product .rvx-review-form__product--image', 'name' => __('Product Height', 'textdomain'), 'property' => 'height', 'control_type' => 'slider-measurebox'))->setRange('50', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_form->addStyleControl(array('selector' => '#rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__product .rvx-review-form__product--image', 'name' => __('Product width', 'textdomain'), 'property' => 'width', 'control_type' => 'slider-measurebox'))->setRange('50', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_filter->addStyleControls((new OxygenBuilder())->prepareClasses('filter'));
        $reviewx_fonts = $this->addControlSection("reviewx_fonts", __("Review Fonts", 'reviewx'), "assets/icon.png", $this);
        $reviewx_fonts->typographySection(__("Criteria Label Typography", 'reviewx'), '#rvx-review-form__wrapper .rvx-review-form__multicriteria--name', $this);
        $reviewx_fonts->typographySection(__("Criteria Value Typography", 'reviewx'), '#rvx-review-form__wrapper .rvx-review-form__multicriteria--name', $this);
        $reviewx_fonts->typographySection(__("Title lable Typography", 'reviewx'), '#rvx-review-form__wrapper .rvx-review-form__title--name', $this);
        $reviewx_fonts->typographySection(__("Title Value", 'reviewx'), '.rvx-review-info .rvx-review-info__title', $this);
        $reviewx_fonts->typographySection(__("Description label", 'reviewx'), '.rvx-review-form__description .rvx-review-form__title--name', $this);
        $reviewx_fonts->typographySection(__("Description Value", 'reviewx'), '.rvx-review-info .rvx-review-info__feedback', $this);
        $reviewx_fonts->typographySection(__("Name", 'reviewx'), '.rvx-review-form__user--name', $this);
        $reviewx_fonts->typographySection(__("Email", 'reviewx'), '.rvx-review-form__email--name', $this);
        $reviewx_fonts->typographySection(__("Attachment", 'reviewx'), '.rvx-review-form__attachment--name', $this);
        $reviewx_fonts->typographySection(__("Recommended", 'reviewx'), '.rvx-review-form__recommended--name', $this);
        $reviewx_fonts->typographySection(__("Mark as Anonymous", 'reviewx'), '.rvx-review-form__mark-anonymous', $this);
        $reviewx_fonts->typographySection(__("Mark as Anonymous", 'reviewx'), '.rvx-review-form__mark-anonymous', $this);
        $reviewx_fonts->typographySection(__("Mark as Anonymous", 'reviewx'), '.rvx-review-form__mark-anonymous', $this);
    }
    function defaultCSS()
    {
        return \file_get_contents(__DIR__ . '/assets/css/' . \basename(__FILE__, '.php') . '.css');
    }
}
new RvxWooReviews();

<?php

namespace Rvx\Oxygen\WooElements;

use Rvx\Builder\Builder;
use Rvx\Builder\Support\OxygenBuilder;
use Rvx\Oxygen\RvxOxyWooEl;
if (!\class_exists('WooCommerce')) {
    return;
}
if (!\class_exists('Rvx\\OxyWooCommerce')) {
    return;
}
class RvxProductTabs extends RvxOxyWooEl
{
    function name()
    {
        return 'ReviewX Product Tabs';
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
        return \call_user_func('woocommerce_output_product_data_tabs', 'reviews');
    }
    function controls()
    {
        /**
         * Tabs Section
         */
        $tabs_section = $this->addControlSection("tabs", __("Tabs", 'reviewx'), "assets/icon.png", $this);
        $tabs_section->addStyleControls(array(array("name" => __('Review Section Title', 'reviewx'), 'slug' => 'rx_section_title', "selector" => '', "property" => 'text'), array("name" => __('Review Section Title Color', 'reviewx'), 'slug' => 'rx_section_title_color', "selector" => '.woocommerce-Reviews-title', "property" => 'color', "control_type" => 'colorpicker'), array("name" => __('Review Section Titles Font Size', 'reviewx'), 'slug' => 'rx_section_title_font_size', "selector" => ".woocommerce-Reviews-title", "property" => 'font-size', "control_type" => "measurebox", "unit" => "px")));
        // Normal sub-section
        $normal_section = $tabs_section->typographySection(__("Normal Tabs"), ".woocommerce-tabs ul.tabs li a", $this);
        $tabs_layout = $normal_section->addControl("buttons-list", "tabs_layout", __("Tabs Layout", 'reviewx'));
        $tabs_layout->setValue(array("Horizontal", "Vertical"));
        $tabs_layout->setValueCSS(array("Horizontal" => "", "Vertical" => "\n                .woocommerce-tabs {\n                display: flex;\n                }\n                .woocommerce-tabs ul.tabs {\n                flex-direction: column;\n                padding: 0;\n                margin-right: 40px;\n                }\n                .woocommerce-tabs ul.tabs li {\n                margin: 5px 0;\n                text-align: left;\n                }\n                .woocommerce-tabs ul.tabs::before {\n                border-bottom: none;\n                }\n                .woocommerce-tabs ul.tabs li.active {\n                border-bottom-color: var(--border-normal);\n                }\n                .woocommerce-tabs .panel {\n                margin-top: 5px;\n                flex-grow: 1;\n                }\n                @media (max-width:640px) {\n                .woocommerce-tabs .panel {\n                  margin-top: 0;\n                }\n                .woocommerce-tabs {\n                  flex-direction: column;\n                }\n                .woocommerce-tabs ul.tabs {\n                  margin-right: 0;\n                  text-align: center;\n                }\n              }\n            "));
        $normal_section->addStyleControls(array(array("name" => __('Tab Background Color', 'reviewx'), "selector" => '.woocommerce-tabs ul.tabs li', "property" => 'background-color', "control_type" => 'colorpicker'), array("name" => __('Tab Border Color', 'reviewx'), "selectors" => array(array("selector" => '.woocommerce-tabs ul.tabs li, .woocommerce-tabs ul.tabs::before', "property" => 'border-color'), array("selector" => '.woocommerce-tabs ul.tabs li.active.active', "property" => 'border-bottom-color')), "control_type" => 'colorpicker')));
        $normal_section->addStyleControl(array("name" => __('Tab Spacing', 'reviewx'), "selectors" => array(array("selector" => '.woocommerce-tabs ul.tabs li', "property" => 'padding-left|padding-right'), array("selector" => '.woocommerce-tabs ul.tabs li:after', "property" => 'height'), array("selector" => '.woocommerce-tabs ul.tabs li:before', "property" => 'height')), "control_type" => 'measurebox'));
        // Active Tab sub-section
        $active_tab_section = $tabs_section->addControlSection("active_tab", __("Active Tab", 'reviewx'), "assets/icon.png", $this);
        $active_tab_section->addStyleControls(array(array("name" => __('Active Tab Text Color', 'reviewx'), "selector" => '.woocommerce-tabs ul.tabs li.active a', "property" => 'color'), array("name" => __('Active Tab Background Color', 'reviewx'), "selector" => '.woocommerce-tabs ul.tabs li.active', "property" => 'background-color|border-bottom-color', "control_type" => 'colorpicker')));
        $show_arrow = $active_tab_section->addControl("checkbox", "show_arrow", __("Show Active Tab Arrow", 'reviewx'));
        $show_arrow->setValueCSS(array("true" => ".woocommerce-tabs ul.tabs li.active:before {\n                          content: '→' ; /* maybe user can select from different simbols? ×©↓ˇ˜• and etc.) */\n                          font-family: 'Courier';\n                          font-size: 24px;\n                          line-height: 0.75;\n                          width: 24px;\n                          height: 24px;\n                          position: relative;\n                          color: var(--standard-link); /* variable value */\n                          display: block;\n                          box-shadow: none;\n                          border: none;\n                          margin-left: 24px;\n                          margin-right: -48px;\n                        }"));
        $active_tab_section->addStyleControls(array(array("name" => __('Active Tab Arrow Color', 'reviewx'), "selector" => '.woocommerce-tabs ul.tabs li.active.active:before', "property" => 'color', "condition" => 'show_arrow=true')));
        // Hovered Tab sub-section
        $hover_tab_section = $tabs_section->addControlSection("hover_tab", __("Hovered Tab", 'reviewx'), "assets/icon.png", $this);
        $hover_tab_section->addStyleControls(array(array("name" => __('Hover Tab Text Color', 'reviewx'), "selector" => '.woocommerce-tabs ul.tabs li:hover a', "property" => 'color'), array("name" => __('Hover Tab Background Color', 'reviewx'), "selector" => '.woocommerce-tabs ul.tabs li:hover', "property" => 'background-color|border-bottom-color', "control_type" => 'colorpicker')));
        $reviewx_graph_criteria = $this->addControlSection("reviewx_graph_criteria", __("Review Summary", 'reviewx'), "assets/icon.png", $this);
        $reviewx_item = $this->addControlSection("reviewx_item", __("Review Card", 'reviewx'), "assets/icon.png", $this);
        $reviewx_filter = $this->addControlSection("reviewx_filter", __("Filter Options", 'reviewx'), "assets/icon.png", $this);
        $reviewx_form = $this->addControlSection("reviewx_form", __("Review Form", 'reviewx'), "assets/icon.png", $this);
        $reviewx_graph_criteria->addStyleControls((new OxygenBuilder())->prepareClasses('graph'));
        $reviewx_item->addStyleControls((new OxygenBuilder())->prepareClasses('reviewItem'));
        $reviewx_item->addStyleControl(array('selector' => '.woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-user__avatar,.woocommerce-Tabs-panel #rvx-review-details .rvx-review-user__avatar', 'name' => __('Avatar Width', 'textdomain'), 'property' => 'width', 'control_type' => 'slider-measurebox'))->setRange('50', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_item->addStyleControl(array('selector' => '.woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-user__avatar,.woocommerce-Tabs-panel #rvx-review-details .rvx-review-user__avatar', 'name' => __('Avatar Height', 'textdomain'), 'property' => 'height', 'control_type' => 'slider-measurebox'))->setRange('50', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_item->addStyleControl(array('selector' => '.woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-user__avatar .rvx-review-user__avatar-fallback span,rvx-review-user__avatar .rvx-review-user__avatar-fallback span,
        .woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__title,#rvx-review-details .rvx-review-info .rvx-review-info__title,
        .woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__date,
        .woocommerce-Tabs-panel #rvx-storefront-widget .rvx-review-card .rvx-review-footer .rvx-review-footer__text,#rvx-storefront-widget #rvx-review-details .rvx-review-footer .rvx-review-footer__text,
        .woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-user .rvx-review-user__name, #rvx-review-details .rvx-review-user .rvx-review-user__name,
        .woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__feedback,#rvx-review-details .rvx-review-info .rvx-review-info__feedback, #rvx-review-details .rvx-review-info__date, #rvx-review-details .rvx-review-form__multicriteria--name', 'name' => __('Font Size', 'textdomain'), 'property' => 'font-size', 'control_type' => 'slider-measurebox'))->setRange('16', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_form->addStyleControls((new OxygenBuilder())->prepareClasses('form'));
        // Form Text: Write a Review
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Write A Review (Text)', 'slug' => 'rvx_oxygen_text_write_a_review', 'default' => 'Write A Review'));
        // Form Text: Rating Title (Text)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Rating Title (Text)', 'slug' => 'rvx_oxygen_text_rating_star_title', 'default' => 'Rating'));
        // Form Text: Review Title (Text)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Rating Title (Text)', 'slug' => 'rvx_oxygen_text_review_title', 'default' => 'Review Title'));
        // Form Text: Review Title (Placeholder)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Rating Title (Placeholder)', 'slug' => 'rvx_oxygen_placeholder_review_title', 'default' => 'Write Review Title'));
        // Form Text: Review Description (Text)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Description (Text)', 'slug' => 'rvx_oxygen_text_review_description', 'default' => 'Description'));
        // Form Text: Review Description (Placeholder)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Description (Placeholder)', 'slug' => 'rvx_oxygen_placeholder_review_description', 'default' => 'Write your description here'));
        // Form Text: Full Name (Text)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Full name (Text)', 'slug' => 'rvx_oxygen_text_full_name', 'default' => 'Full name'));
        // Form Text: Full Name (Placeholder)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Full name (Placeholder)', 'slug' => 'rvx_oxygen_placeholder_full_name', 'default' => 'Full Name'));
        // Form Text: Email Address (Text)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Email address (Text)', 'slug' => 'rvx_oxygen_text_email_name', 'default' => 'Email address'));
        // Form Text: Email Address (Placeholder)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Email address (Placeholder)', 'slug' => 'rvx_oxygen_placeholder_email_name', 'default' => 'Email Address'));
        // Form Text: Attachment (Text)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Attachment (Text)', 'slug' => 'rvx_oxygen_text_attachment_title', 'default' => 'Attachment'));
        // Form Text: Upload Photo / Video (Placeholder)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Upload Photo / Video (Text)', 'slug' => 'rvx_oxygen_placeholder_upload_photo', 'default' => 'Upload Photo / Video'));
        // Form Text: Mark as Anonymous (Text)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Mark as Anonymous (Text)', 'slug' => 'rvx_oxygen_text_mark_as_anonymous', 'default' => 'Mark as Anonymous'));
        // Form Text: Recommended? (Text)
        $reviewx_form->addOptionControl(array('type' => 'textfield', 'name' => 'Recommended? (Text)', 'slug' => 'rvx_oxygen_text_recommended_title', 'default' => 'Recommendation?'));
        $reviewx_filter->addStyleControls((new OxygenBuilder())->prepareClasses('filter'));
        $reviewx_form->borderSection(__("Button Border", 'reviewx'), '#rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"]', $this);
        $reviewx_form->addStyleControl(array('selector' => '.woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-user__avatar .rvx-review-user__avatar-fallback span,rvx-review-user__avatar .rvx-review-user__avatar-fallback span,
                .woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__title,#rvx-review-details .rvx-review-info .rvx-review-info__title,
                .woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__date,
               .woocommerce-Tabs-panel  #rvx-storefront-widget .rvx-review-card .rvx-review-footer .rvx-review-footer__text,#rvx-storefront-widget #rvx-review-details .rvx-review-footer .rvx-review-footer__text,
                .woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-user .rvx-review-user__name, #rvx-review-details .rvx-review-user .rvx-review-user__name,
                .woocommerce-Tabs-panel .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__feedback,#rvx-review-details .rvx-review-info .rvx-review-info__feedback, #rvx-review-details .rvx-review-info__date, #rvx-review-details .rvx-review-form__multicriteria--name', 'name' => __('Font Size', 'textdomain'), 'property' => 'font-size', 'control_type' => 'slider-measurebox'))->setRange('16', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_form->addStyleControl(array('selector' => 'woocommerce-Tabs-panel #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__product .rvx-review-form__product--image', 'name' => __('Product Height', 'textdomain'), 'property' => 'height', 'control_type' => 'slider-measurebox'))->setRange('50', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_form->addStyleControl(array('selector' => 'woocommerce-Tabs-panel #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__product .rvx-review-form__product--image', 'name' => __('Product width', 'textdomain'), 'property' => 'width', 'control_type' => 'slider-measurebox'))->setRange('50', '1000', '10')->setUnits('px', '%', 'em', 'rem');
        $reviewx_fonts = $this->addControlSection("reviewx_fonts", __("Review Fonts", 'reviewx'), "assets/icon.png", $this);
        $reviewx_fonts->typographySection(__("Criteria Label Typography", 'reviewx'), '#rvx-review-form__wrapper .rvx-review-form__multicriteria--name', $this);
        $reviewx_fonts->typographySection(__("Criteria Value Typography", 'reviewx'), '#rvx-review-form__wrapper .rvx-review-form__multicriteria--name', $this);
        $reviewx_fonts->typographySection(__("Title lable Typography", 'reviewx'), '#rvx-review-form__wrapper .rvx-review-form__title--name', $this);
        $reviewx_fonts->typographySection(__("Title Value", 'reviewx'), '.rvx-review-info .rvx-review-info__title', $this);
        $reviewx_fonts->typographySection(__("Description label", 'reviewx'), '.rvx-review-form__description .rvx-review-form__title--name', $this);
        $reviewx_fonts->typographySection(__("Description Value", 'reviewx'), '.rvx-review-info .rvx-review-info__feedback', $this);
        $reviewx_fonts->typographySection(__("Attachment", 'reviewx'), '.rvx-review-form__attachment--name', $this);
        $reviewx_fonts->typographySection(__("Recommended", 'reviewx'), '.rvx-review-form__recommended--name', $this);
        $reviewx_fonts->typographySection(__("Mark as Anonymous", 'reviewx'), '.rvx-review-form__mark-anonymous', $this);
    }
    function defaultCSS()
    {
        return \file_get_contents(__DIR__ . '/assets/css/' . \basename(__FILE__, '.php') . '.css');
    }
}
new RvxProductTabs();

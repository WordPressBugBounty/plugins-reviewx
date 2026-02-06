<?php

namespace Rvx\Handlers\Customize;

use WP_Customize_Color_Control;
class WidgetCustomizeOptionsHandler
{
    public function __invoke($wp_customize) : void
    {
        //if (!did_action('elementor/loaded')) {
        $this->rvx_customizer_options_data($wp_customize);
        //}
    }
    public static function rvx_customize_preview_js() : void
    {
        add_action('customize_preview_init', function () {
            wp_enqueue_script('reviewx-customize', RVX_CUSTOMIZER_URL . 'assets/js/rvx-customize.js', array('jquery', 'customize-preview'), '', \true);
        });
    }
    public function rvx_customizer_options_data($wp_customize) : void
    {
        // ReviewX Customize options - live preview js
        $this->rvx_customize_preview_js();
        // ReviewX Customize options Data
        $wp_customize->add_panel('reviewx_form_input', ['title' => __('ReviewX', 'reviewx'), 'description' => __('Customize ReviewX Settings', 'reviewx'), 'priority' => 160]);
        // Add Section to the Panel [ReviewX -> Reviews Overview Section]
        /** 
        $wp_customize->add_section('rvx_general_settings_section', array(
            'title' => __('General Settings', 'reviewx'),
            'panel' => 'reviewx_form_input',
            'priority' => 10,
        ));
        */
        // Add Section to the Panel [ReviewX -> Reviews Overview Section]
        $wp_customize->add_section('rvx_reviews_overview_section', array('title' => __('Reviews Overview', 'reviewx'), 'panel' => 'reviewx_form_input', 'priority' => 10));
        // Add Section to the Panel [ReviewX -> Filter Buttons Section]
        $wp_customize->add_section('rvx_filter_section', array('title' => __('Filter Buttons', 'reviewx'), 'panel' => 'reviewx_form_input', 'priority' => 10));
        // Add Section to the Panel [ReviewX -> Review Items Section]
        $wp_customize->add_section('rvx_review_items_section', array('title' => __('Review Items', 'reviewx'), 'panel' => 'reviewx_form_input', 'priority' => 10));
        // Add Section to the Panel [ReviewX -> Review Form Section]
        $wp_customize->add_section('rvx_form_section', array('title' => __('Review Form', 'reviewx'), 'panel' => 'reviewx_form_input', 'priority' => 10));
        /**
         * ReviewX - General Settings
         */
        // Active Rating Stars: [Background Color]
        /**
        $wp_customize->add_setting('rvx_general_reviews_active_rating_stars_background_color', array(
            'default'           => '#FCCE08',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_general_reviews_active_rating_stars_background_color', array(
            'label'    => __('Active Rating Stars: [Background Color]', 'reviewx'),
            'section'  => 'rvx_general_settings_section',
            'settings' => 'rvx_general_reviews_active_rating_stars_background_color',
        )));
        */
        /**
         * ReviewX - Reviews Overview
         */
        // Rating out of: [Text Color]
        $wp_customize->add_setting('rvx_reviews_overview_rating_out_of_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_overview_rating_out_of_text_color', array('label' => __('Rating out of: [Text Color]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_rating_out_of_text_color')));
        // Rating out of: [Font Size]
        $wp_customize->add_setting('rvx_reviews_overview_rating_out_of_text_font_size', array('default' => 43.942, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_overview_rating_out_of_text_font_size', array('label' => __('Rating out of: [Font Size]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_rating_out_of_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 100, 'step' => 1.0)));
        // Rating out of Total: [Text Color]
        $wp_customize->add_setting('rvx_reviews_overview_rating_out_of_total_text_color', array('default' => '#BDBDBD', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_overview_rating_out_of_total_text_color', array('label' => __('Rating out of Total: [Text Color]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_rating_out_of_total_text_color')));
        // Rating out of Total: [Font Size]
        $wp_customize->add_setting('rvx_reviews_overview_rating_out_of_total_text_font_size', array('default' => 26.365, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_overview_rating_out_of_total_text_font_size', array('label' => __('Rating out of Total: [Font Size]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_rating_out_of_total_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 100, 'step' => 1.0)));
        // Rating Badge: [Background Color]
        $wp_customize->add_setting('rvx_reviews_overview_rating_badge_background_color', array('default' => '#22C55E', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_overview_rating_badge_background_color', array('label' => __('Rating Badge: [Background Color]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_rating_badge_background_color')));
        // Rating Badge: [Text Color]
        $wp_customize->add_setting('rvx_reviews_overview_rating_badge_text_color', array('default' => '#FFFFFF', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_overview_rating_badge_text_color', array('label' => __('Rating Badge: [Text Color]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_rating_badge_text_color')));
        // Total Reviews Count: [Text Color]
        $wp_customize->add_setting('rvx_reviews_overview_total_reviews_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_overview_total_reviews_text_color', array('label' => __('Total Reviews Count: [Text Color]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_total_reviews_text_color')));
        // Total Reviews Count: [Font Size]
        $wp_customize->add_setting('rvx_reviews_overview_total_reviews_text_font_size', array('default' => 16, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_overview_total_reviews_text_font_size', array('label' => __('Total Reviews Count: [Font Size]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_total_reviews_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Rating Overview Chart: [Text Color]
        $wp_customize->add_setting('rvx_reviews_overview_rating_overview_chart_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_overview_rating_overview_chart_text_color', array('label' => __('Rating Overview Chart: [Text Color]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_rating_overview_chart_text_color')));
        // Rating Overview Chart: [Font Size]
        $wp_customize->add_setting('rvx_reviews_overview_rating_overview_chart_text_font_size', array('default' => 14, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_overview_rating_overview_chart_text_font_size', array('label' => __('Rating Overview Chart: [Font Size]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_rating_overview_chart_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Product Recommendation: [Background Color]
        $wp_customize->add_setting('rvx_reviews_overview_product_recommendation_background_color', array('default' => '#F5F5F5', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_overview_product_recommendation_background_color', array('label' => __('Product Recommendation: [Background Color]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_product_recommendation_background_color')));
        // Product Recommendation: [Border Color]
        $wp_customize->add_setting('rvx_reviews_overview_product_recommendation_border_color', array('default' => '#F5F5F5', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_overview_product_recommendation_border_color', array('label' => __('Product Recommendation: [Border Color]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_product_recommendation_border_color')));
        // Product Recommendation: [Border Radius]
        $wp_customize->add_setting('rvx_reviews_overview_product_recommendation_border_radius', array('default' => 4, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_overview_product_recommendation_border_radius', array('label' => __('Product Recommendation: [Border Radius]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_product_recommendation_border_radius', 'type' => 'range', 'input_attrs' => array('min' => 0, 'max' => 50, 'step' => 1)));
        // Product Recommendation Text: [Text Color]
        $wp_customize->add_setting('rvx_reviews_overview_product_recommendation_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_overview_product_recommendation_text_color', array('label' => __('Product Recommendation Text: [Text Color]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_product_recommendation_text_color')));
        // Product Recommendation Text: [Font Size]
        $wp_customize->add_setting('rvx_reviews_overview_product_recommendation_text_font_size', array('default' => 14, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_overview_product_recommendation_text_font_size', array('label' => __('Product Recommendation Text: [Font Size]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_product_recommendation_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Review Criteria Text: [Text Color]
        $wp_customize->add_setting('rvx_reviews_overview_review_criteria_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_overview_review_criteria_text_color', array('label' => __('Review Criteria Text: [Text Color]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_review_criteria_text_color')));
        // Review Criteria Text: [Font Size]
        $wp_customize->add_setting('rvx_reviews_overview_review_criteria_text_font_size', array('default' => 14, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_overview_review_criteria_text_font_size', array('label' => __('Review Criteria Text: [Font Size]', 'reviewx'), 'section' => 'rvx_reviews_overview_section', 'settings' => 'rvx_reviews_overview_review_criteria_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        /*
         * ReviewX - Filter Buttons
         */
        // Filter Button: [Text Color]
        $wp_customize->add_setting('rvx_filter_button_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_button_text_color', array('label' => __('Filter Button: [Text Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_button_text_color')));
        // Filter Button: [Background Color]
        $wp_customize->add_setting('rvx_filter_button_background_color', array('default' => '#F0F0F1', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_button_background_color', array('label' => __('Filter Button: [Background Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_button_background_color')));
        // Filter Button: [Border Color]
        $wp_customize->add_setting('rvx_filter_button_border_color', array('default' => '#BDBDBD', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_button_border_color', array('label' => __('Filter Button: [Border Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_button_border_color')));
        // Filter Button: [Border Radius]
        $wp_customize->add_setting('rvx_filter_button_border_radius', array('default' => 4, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_filter_button_border_radius', array('label' => __('Filter Button: [Border Radius]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_button_border_radius', 'type' => 'range', 'input_attrs' => array('min' => 0, 'max' => 50, 'step' => 1)));
        // Filter by: [Text Color]
        $wp_customize->add_setting('rvx_filter_by_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_by_text_color', array('label' => __('Filter by: [Text Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_by_text_color')));
        // Filter by: [Font Size]
        $wp_customize->add_setting('rvx_filter_by_text_font_size', array('default' => 16, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_filter_by_text_font_size', array('label' => __('Filter by: [Font Size]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_by_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Filter Dropdown Menu: [Text Color]
        $wp_customize->add_setting('rvx_filter_dropdown_menu_text_color', array('default' => '#616161', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_dropdown_menu_text_color', array('label' => __('Filter Dropdown Menu: [Text Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_dropdown_menu_text_color')));
        // Filter Dropdown Menu: [Background Color]
        $wp_customize->add_setting('rvx_filter_dropdown_menu_background_color', array('default' => '#FFFFFF', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_dropdown_menu_background_color', array('label' => __('Filter Dropdown Menu: [Background Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_dropdown_menu_background_color')));
        // Filter Dropdown Menu: [Border Color]
        $wp_customize->add_setting('rvx_filter_dropdown_menu_border_color', array('default' => '#FFFFFF', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_dropdown_menu_border_color', array('label' => __('Filter Dropdown Menu: [Border Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_dropdown_menu_border_color')));
        // Filter Dropdown Menu: [Border Radius]
        $wp_customize->add_setting('rvx_filter_dropdown_menu_border_radius', array('default' => 4, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_filter_dropdown_menu_border_radius', array('label' => __('Filter Dropdown Menu: [Border Radius]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_dropdown_menu_border_radius', 'type' => 'range', 'input_attrs' => array('min' => 0, 'max' => 50, 'step' => 1)));
        // Filter Dropdown Menu: [Font Size]
        $wp_customize->add_setting('rvx_filter_dropdown_menu_text_font_size', array('default' => 14, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_filter_dropdown_menu_text_font_size', array('label' => __('Filter Dropdown Menu: [Font Size]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_dropdown_menu_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Filter Dropdown Menu: [Reset filters] button - [Text Color]
        $wp_customize->add_setting('rvx_filter_reset_button_text_color', array('default' => '#0043DD', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_reset_button_text_color', array('label' => __('Reset filters button: [Text Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_reset_button_text_color')));
        // Filter Dropdown Menu: [Reset filters] button - [Background Color]
        $wp_customize->add_setting('rvx_filter_reset_button_background_color', array('default' => '#FFFFFF', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_reset_button_background_color', array('label' => __('Reset filters button: [Background Color]]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_reset_button_background_color')));
        // Filter Dropdown Menu: [Reset filters] button - [Border Color]
        $wp_customize->add_setting('rvx_filter_reset_button_border_color', array('default' => '#FFFFFF', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_reset_button_border_color', array('label' => __('Reset filters button: [Border Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_reset_button_border_color')));
        // Filter Dropdown Menu: [Reset filters] button - [Border Radius]
        $wp_customize->add_setting('rvx_filter_reset_button_border_radius', array('default' => 4, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_filter_reset_button_border_radius', array('label' => __('Reset filters button: [Border Radius]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_reset_button_border_radius', 'type' => 'range', 'input_attrs' => array('min' => 0, 'max' => 50, 'step' => 1)));
        // Filter Dropdown Menu: [Apply] button - [Text Color]
        $wp_customize->add_setting('rvx_filter_apply_button_text_color', array('default' => '#FFFFFF', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_apply_button_text_color', array('label' => __('Apply filters button: [Text Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_apply_button_text_color')));
        // Filter Dropdown Menu: [Apply] button - [Background Color]
        $wp_customize->add_setting('rvx_filter_apply_button_background_color', array('default' => '#0043DD', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_apply_button_background_color', array('label' => __('Apply filters button: [Background Color]]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_apply_button_background_color')));
        // Filter Dropdown Menu: [Apply] button - [Border Color]
        $wp_customize->add_setting('rvx_filter_apply_button_border_color', array('default' => '#0043DD', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_filter_apply_button_border_color', array('label' => __('Apply filters button: [Border Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_apply_button_border_color')));
        // Filter Dropdown Menu: [Apply] button - [Border Radius]
        $wp_customize->add_setting('rvx_filter_apply_button_border_radius', array('default' => 4, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_filter_apply_button_border_radius', array('label' => __('Apply filters button: [Border Radius]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_filter_apply_button_border_radius', 'type' => 'range', 'input_attrs' => array('min' => 0, 'max' => 50, 'step' => 1)));
        // Sort Button: [Text Color]
        $wp_customize->add_setting('rvx_sort_button_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_sort_button_text_color', array('label' => __('Sort Button: [Text Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_sort_button_text_color')));
        // Sort Button: [Background Color]
        $wp_customize->add_setting('rvx_sort_button_background_color', array('default' => '#F0F0F1', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_sort_button_background_color', array('label' => __('Sort Button: [Background Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_sort_button_background_color')));
        // Sort Button: [Border Color]
        $wp_customize->add_setting('rvx_sort_button_border_color', array('default' => '#BDBDBD', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_sort_button_border_color', array('label' => __('Sort Button: [Border Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_sort_button_border_color')));
        // Sort Button: [Border Radius]
        $wp_customize->add_setting('rvx_sort_button_border_radius', array('default' => 4, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_sort_button_border_radius', array('label' => __('Sort Button: [Border Radius]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_sort_button_border_radius', 'type' => 'range', 'input_attrs' => array('min' => 0, 'max' => 50, 'step' => 1)));
        // Sort Dropdown Menu: [Text Color]
        $wp_customize->add_setting('rvx_sort_dropdown_menu_text_color', array('default' => '#616161', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_sort_dropdown_menu_text_color', array('label' => __('Sort Dropdown Menu: [Text Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_sort_dropdown_menu_text_color')));
        // Sort Dropdown Menu: [Background Color]
        $wp_customize->add_setting('rvx_sort_dropdown_menu_background_color', array('default' => '#FFFFFF', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_sort_dropdown_menu_background_color', array('label' => __('Sort Dropdown Menu: [Background Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_sort_dropdown_menu_background_color')));
        // Sort Dropdown Menu: [Border Color]
        $wp_customize->add_setting('rvx_sort_dropdown_menu_border_color', array('default' => '#FFFFFF', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_sort_dropdown_menu_border_color', array('label' => __('Sort Dropdown Menu: [Border Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_sort_dropdown_menu_border_color')));
        // Sort Dropdown Menu: [Border Radius]
        $wp_customize->add_setting('rvx_sort_dropdown_menu_border_radius', array('default' => 4, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_sort_dropdown_menu_border_radius', array('label' => __('Sort Dropdown Menu: [Border Radius]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_sort_dropdown_menu_border_radius', 'type' => 'range', 'input_attrs' => array('min' => 0, 'max' => 50, 'step' => 1)));
        // Sort Dropdown Menu: [Font Size]
        $wp_customize->add_setting('rvx_sort_dropdown_menu_text_font_size', array('default' => 14, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_sort_dropdown_menu_text_font_size', array('label' => __('Sort Dropdown Menu: [Font Size]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_sort_dropdown_menu_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Write a Review Button: [Text Color]
        /**
        $wp_customize->add_setting('rvx_write_review_button_text_color', array(
            'default'           => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_write_review_button_text_color', array(
            'label'    => __('Write a Review Button: [Text Color]', 'reviewx'),
            'section'  => 'rvx_filter_section',
            'settings' => 'rvx_write_review_button_text_color',
        )));
        
        // Write a Review Button: [Background Color]
        $wp_customize->add_setting('rvx_write_review_button_background_color', array(
            'default'           => '#0043DD',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_write_review_button_background_color', array(
            'label'    => __('Write a Review Button: [Background Color]', 'reviewx'),
            'section'  => 'rvx_filter_section',
            'settings' => 'rvx_write_review_button_background_color',
        )));
        */
        // Write a Review Button: [Border Color]
        $wp_customize->add_setting('rvx_write_review_button_border_color', array('default' => '#0043DD', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_write_review_button_border_color', array('label' => __('Write a Review Button: [Border Color]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_write_review_button_border_color')));
        // Write a Review Button: [Border Radius]
        $wp_customize->add_setting('rvx_write_review_button_border_radius', array('default' => 4, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_write_review_button_border_radius', array('label' => __('Write a Review Button: [Border Radius]', 'reviewx'), 'section' => 'rvx_filter_section', 'settings' => 'rvx_write_review_button_border_radius', 'type' => 'range', 'input_attrs' => array('min' => 0, 'max' => 50, 'step' => 1)));
        /**
         * ReviewX - Review Items
         */
        // Review Items: Card - [Background Color]
        $wp_customize->add_setting('rvx_reviews_items_card_background_color', array('default' => '#F5F5F5', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_items_card_background_color', array('label' => __('Review Items: Card - [Background Color]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_card_background_color')));
        // Review Items: Card - [Border Color]
        $wp_customize->add_setting('rvx_reviews_items_card_border_color', array('default' => '#F5F5F5', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_items_card_border_color', array('label' => __('Review Card: [Border Color]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_card_border_color')));
        // Review Items: Card - [Padding]
        $wp_customize->add_setting('rvx_reviews_items_card_inline_padding', array('default' => 8, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_items_card_inline_padding', array('label' => __('Review Card: [Padding]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_card_inline_padding', 'type' => 'range', 'input_attrs' => array('min' => 4, 'max' => 50, 'step' => 1)));
        // Review Items: Card - [Border Radius]
        $wp_customize->add_setting('rvx_reviews_items_card_border_radius', array('default' => 6, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_items_card_border_radius', array('label' => __('Review Card: [Border Radius]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_card_border_radius', 'type' => 'range', 'input_attrs' => array('min' => 0, 'max' => 50, 'step' => 1)));
        // Review Items: Reviewer Name - [Text Color]
        $wp_customize->add_setting('rvx_reviews_items_reviewer_name_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_items_reviewer_name_text_color', array('label' => __('Reviewer Name: [Text Color]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_reviewer_name_text_color')));
        // Review Items: Reviewer Name - [Text Size]
        $wp_customize->add_setting('rvx_reviews_items_reviewer_name_text_font_size', array('default' => 20, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_items_reviewer_name_text_font_size', array('label' => __('Reviewer Name: [Text Size]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_reviewer_name_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Review Items: Review Title - [Text Color]
        $wp_customize->add_setting('rvx_reviews_items_title_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_items_title_text_color', array('label' => __('Review Title: [Text Color]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_title_text_color')));
        // Review Items: Review Title - [Text Size]
        $wp_customize->add_setting('rvx_reviews_items_title_text_font_size', array('default' => 20, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_items_title_text_font_size', array('label' => __('Review Title: [Text Size]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_title_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Review Items: Review Date - [Text Color]
        $wp_customize->add_setting('rvx_reviews_items_date_text_color', array('default' => '#757575', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_items_date_text_color', array('label' => __('Review Date: [Text Color]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_date_text_color')));
        // Review Items: Review Date - [Text Size]
        $wp_customize->add_setting('rvx_reviews_items_date_text_font_size', array('default' => 14, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_items_date_text_font_size', array('label' => __('Review Date: [Text Size]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_date_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Review Items: Description - [Text Color]
        $wp_customize->add_setting('rvx_reviews_items_description_text_color', array('default' => '#757575', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_reviews_items_description_text_color', array('label' => __('Review Description: [Text Color]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_description_text_color')));
        // Review Items: Description - [Text Size]
        $wp_customize->add_setting('rvx_reviews_items_description_text_font_size', array('default' => 14, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_reviews_items_description_text_font_size', array('label' => __('Review Description: [Text Size]', 'reviewx'), 'section' => 'rvx_review_items_section', 'settings' => 'rvx_reviews_items_description_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        /**
         * ReviewX - Review Form
         */
        // Form: Background Color
        $wp_customize->add_setting('rvx_input_form_background_color', array('default' => '#F5F5F5', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_background_color', array('label' => __('Form: Background Color', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_background_color')));
        // Form: [Border Color]
        $wp_customize->add_setting('rvx_input_form_border_color', array('default' => '#F5F5F5', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_border_color', array('label' => __('Form: [Border Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_border_color')));
        // Form: Border Radius
        $wp_customize->add_setting('rvx_input_form_border_radius', array('default' => 6, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_input_form_border_radius', array('label' => __('Form: Border Radius', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_border_radius', 'type' => 'range', 'input_attrs' => array('min' => 0, 'max' => 50, 'step' => 1)));
        // Form: Title [Text Color]
        $wp_customize->add_setting('rvx_input_form_title_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_title_text_color', array('label' => __('Form: Title [Text Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_title_text_color')));
        // Form: Title [Text Size]
        $wp_customize->add_setting('rvx_input_form_title_text_font_size', array('default' => 18, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_input_form_title_text_font_size', array('label' => __('Form: Title [Text Size]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_title_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 100, 'step' => 1)));
        // Form: Product Name [Text Color]
        $wp_customize->add_setting('rvx_input_form_product_name_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_product_name_text_color', array('label' => __('Form: Product Name [Text Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_product_name_text_color')));
        // Form: Product Name [Text Size]
        $wp_customize->add_setting('rvx_input_form_product_name_text_font_size', array('default' => 18, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_input_form_product_name_text_font_size', array('label' => __('Form: Product Name [Text Size]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_product_name_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Form: Criteria Title [Text Color]
        $wp_customize->add_setting('rvx_input_form_criteria_title_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_criteria_title_text_color', array('label' => __('Form: Criteria Title [Text Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_criteria_title_text_color')));
        // Form: Criteria Title [Text Size]
        $wp_customize->add_setting('rvx_input_form_criteria_title_text_font_size', array('default' => 16, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_input_form_criteria_title_text_font_size', array('label' => __('Form: Criteria Title [Text Size]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_criteria_title_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Form: Review Title [Text Color]
        $wp_customize->add_setting('rvx_input_form_review_title_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_review_title_text_color', array('label' => __('Form: Review Title [Text Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_review_title_text_color')));
        // Form: Review Title [Text Size]
        $wp_customize->add_setting('rvx_input_form_review_title_text_font_size', array('default' => 16, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_input_form_review_title_text_font_size', array('label' => __('Form: Review Title [Text Size]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_review_title_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Form: Description [Text Color]
        $wp_customize->add_setting('rvx_input_form_description_title_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_description_title_text_color', array('label' => __('Form: Description [Text Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_description_title_text_color')));
        // Form: Description [Text Size]
        $wp_customize->add_setting('rvx_input_form_description_title_text_font_size', array('default' => 16, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_input_form_description_title_text_font_size', array('label' => __('Form: Description [Text Size]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_description_title_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Form: Full Name [Text Color]
        $wp_customize->add_setting('rvx_input_form_full_name_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_full_name_text_color', array('label' => __('Form: Full Name [Text Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_full_name_text_color')));
        // Form: Full Name [Text Size]
        $wp_customize->add_setting('rvx_input_form_full_name_text_font_size', array('default' => 16, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_input_form_full_name_text_font_size', array('label' => __('Form: Full Name [Text Size]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_full_name_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Form: Email Address [Text Color]
        $wp_customize->add_setting('rvx_input_form_email_address_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_email_address_text_color', array('label' => __('Form: Email Address [Text Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_email_address_text_color')));
        // Form: Email Address [Text Size]
        $wp_customize->add_setting('rvx_input_form_email_address_text_font_size', array('default' => 16, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_input_form_email_address_text_font_size', array('label' => __('Form: Email Address [Text Size]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_email_address_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Form: Attachment Title [Text Color]
        $wp_customize->add_setting('rvx_input_form_attachment_title_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_attachment_title_text_color', array('label' => __('Form: Attachment [Text Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_attachment_title_text_color')));
        // Form: Attachment Title [Text Size]
        $wp_customize->add_setting('rvx_input_form_attachment_title_text_font_size', array('default' => 16, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_input_form_attachment_title_text_font_size', array('label' => __('Form: Attachment [Text Size]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_attachment_title_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Form: Mark as Anonymous [Text Color]
        $wp_customize->add_setting('rvx_input_form_mark_anonymous_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_mark_anonymous_text_color', array('label' => __('Form: Mark as Anonymous [Text Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_mark_anonymous_text_color')));
        // Form: Mark as Anonymous [Text Size]
        $wp_customize->add_setting('rvx_input_form_mark_anonymous_text_font_size', array('default' => 16, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_input_form_mark_anonymous_text_font_size', array('label' => __('Form: Mark as Anonymous [Text Size]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_mark_anonymous_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Form: Recommended? [Text Color]
        $wp_customize->add_setting('rvx_input_form_recommended_text_color', array('default' => '#424242', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_input_form_recommended_text_color', array('label' => __('Form: Recommended? [Text Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_recommended_text_color')));
        // Form: Recommended? [Text Size]
        $wp_customize->add_setting('rvx_input_form_recommended_text_font_size', array('default' => 16, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_input_form_recommended_text_font_size', array('label' => __('Form: Recommended? [Text Size]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_input_form_recommended_text_font_size', 'type' => 'range', 'input_attrs' => array('min' => 10, 'max' => 50, 'step' => 1)));
        // Form: Submit Review Button [Text Color]
        /**
        $wp_customize->add_setting('rvx_submit_review_button_text_color', array(
            'default'           => '#FFFFFF',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_submit_review_button_text_color', array(
            'label'    => __('Submit Review Button: [Text Color]', 'reviewx'),
            'section'  => 'rvx_form_section',
            'settings' => 'rvx_submit_review_button_text_color',
        )));
        
        // Form: Submit Review Button [Background Color]
        $wp_customize->add_setting('rvx_submit_review_button_background_color', array(
            'default'           => '#0043DD',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_submit_review_button_background_color', array(
            'label'    => __('Submit Review Button: [Background Color]', 'reviewx'),
            'section'  => 'rvx_form_section',
            'settings' => 'rvx_submit_review_button_background_color',
        )));
        */
        // Form: Submit Review Button [Border Color]
        $wp_customize->add_setting('rvx_submit_review_button_border_color', array('default' => '#0043DD', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage'));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'rvx_submit_review_button_border_color', array('label' => __('Submit Review Button: [Border Color]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_submit_review_button_border_color')));
        // Form: Submit Review Button [Border Radius]
        $wp_customize->add_setting('rvx_submit_review_button_border_radius', array('default' => 6, 'sanitize_callback' => 'absint', 'transport' => 'postMessage'));
        $wp_customize->add_control('rvx_submit_review_button_border_radius', array('label' => __('Submit Review Button: [Border Radius]', 'reviewx'), 'section' => 'rvx_form_section', 'settings' => 'rvx_submit_review_button_border_radius', 'type' => 'range', 'input_attrs' => array('min' => 0, 'max' => 50, 'step' => 1)));
    }
}

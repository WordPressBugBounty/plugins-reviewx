<?php

namespace Rvx\Elementor\Elements;

// If this file is called directly, abort.
if (!\defined('ABSPATH')) {
    exit;
}
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Widget_Base;
/**
 * Class Review
 * @package ReviewX\Elementor\Elements
 */
class Review_Widget extends Widget_Base
{
    /**
     * @return string
     */
    public function get_name()
    {
        return 'rx-review-widget';
    }
    /**
     * @return string|void
     */
    public function get_title()
    {
        return __('ReviewX Woo Review', 'reviewx');
    }
    /**
     * @return string
     */
    public function get_icon()
    {
        return 'eicon-review';
    }
    /**
     * @return array|string[]
     */
    public function get_categories()
    {
        return ['rx-addons-elementor'];
    }
    /**
     * @return array
     */
    public function get_keywords()
    {
        return ['reviewx', 'woo review', 'woo', 'woocommerce', 'comment', 'review', 'addons', 'ea', 'essential addons'];
    }
    /**
     * @return string
     */
    public function get_custom_help_url()
    {
        return esc_url('https://reviewx.io/docs');
    }
    /**
     * @param $styles
     * @param bool $group
     * @return array|array[]|mixed
     */
    private function get_options_by_groups($styles, $group = \false)
    {
        $groups = ['line' => ['label' => __('Line', 'reviewx'), 'options' => ['solid' => __('Solid', 'reviewx'), 'double' => __('Double', 'reviewx'), 'dotted' => __('Dotted', 'reviewx'), 'dashed' => __('Dashed', 'reviewx')]]];
        if (!empty($styles)) {
            foreach ($styles as $key => $style) {
                if (!isset($groups[$style['group']])) {
                    $groups[$style['group']] = ['label' => \ucwords(\str_replace('_', '', $style['group'])), 'options' => []];
                }
                $groups[$style['group']]['options'][$key] = $style['label'];
            }
        }
        if ($group && isset($groups[$group])) {
            return $groups[$group];
        }
        return $groups;
    }
    /**
     * @param $array
     * @param $key
     * @param $value
     * @return array
     */
    private function filter_styles_by($array, $key, $value)
    {
        return \array_filter($array, function ($style) use($key, $value) {
            return $value === $style[$key];
        });
    }
    /**
     * Register Controls
     * @return void
     */
    protected function register_controls()
    {
        $styles = '';
        $this->start_controls_section('rvx_section_review_tabs_style', ['label' => __('Tabs', 'reviewx'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('rvx_wc_style_warning', ['type' => Controls_Manager::RAW_HTML, 'raw' => __('The style of this widget is often affected by your theme and plugins. If you experience any such issue, try to switch to a basic theme and deactivate related plugins.', 'reviewx'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-info']);
        $this->start_controls_tabs('tabs_style');
        $this->start_controls_tab('normal_tabs_style', ['label' => __('Normal', 'reviewx')]);
        $this->add_control('rvx_tab_text_color', ['label' => __('Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'selectors' => ['.woocommerce {{WRAPPER}} .woocommerce-tabs ul.wc-tabs li a' => 'color: {{VALUE}}']]);
        $this->add_control('rvx_tab_bg_color', ['label' => __('Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'alpha' => \false, 'selectors' => ['.woocommerce {{WRAPPER}} .woocommerce-tabs ul.wc-tabs li' => 'background-color: {{VALUE}}']]);
        $this->add_control('rvx_tabs_border_color', ['label' => __('Border Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'selectors' => ['.woocommerce {{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'border-color: {{VALUE}}', '.woocommerce {{WRAPPER}} .woocommerce-tabs ul.wc-tabs li' => 'border-color: {{VALUE}}']]);
        $this->end_controls_tab();
        $this->start_controls_tab('active_tabs_style', ['label' => __('Active', 'reviewx')]);
        $this->add_control('rvx_active_tab_text_color', ['label' => __('Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'selectors' => ['.woocommerce {{WRAPPER}} .woocommerce-tabs ul.wc-tabs li.active a' => 'color: {{VALUE}}']]);
        $this->add_control('rvx_active_tab_bg_color', ['label' => __('Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'alpha' => \false, 'selectors' => ['.woocommerce {{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel, .woocommerce {{WRAPPER}} .woocommerce-tabs ul.wc-tabs li.active' => 'background-color: {{VALUE}}', '.woocommerce {{WRAPPER}} .woocommerce-tabs ul.wc-tabs li.active' => 'border-bottom-color: {{VALUE}}']]);
        $this->add_control('rvx_active_tabs_border_color', ['label' => __('Border Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'selectors' => ['.woocommerce {{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'border-color: {{VALUE}}', '.woocommerce {{WRAPPER}} .woocommerce-tabs ul.wc-tabs li.active' => 'border-color: {{VALUE}} {{VALUE}} {{active_tab_bg_color.VALUE}} {{VALUE}}', '.woocommerce {{WRAPPER}} .woocommerce-tabs ul.wc-tabs li:not(.active)' => 'border-bottom-color: {{VALUE}}']]);
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->add_control('rvx_separator_tabs_style', ['type' => Controls_Manager::DIVIDER]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_tab_typography', 'label' => __('Typography', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .woocommerce-tabs ul.wc-tabs li a']);
        $this->add_control('rvx_tab_border_radius', ['label' => __('Border Radius', 'reviewx'), 'type' => Controls_Manager::SLIDER, 'selectors' => ['.woocommerce {{WRAPPER}} .woocommerce-tabs ul.wc-tabs li' => 'border-radius: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0 0']]);
        $this->end_controls_section();
        $this->start_controls_section('rvx_section_product_panel_style', ['label' => __('Panel', 'reviewx'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('rvx_text_color', ['label' => __('Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'selectors' => ['.woocommerce {{WRAPPER}} .woocommerce-Tabs-panel' => 'color: {{VALUE}}']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_content_typography', 'label' => __('Typography', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel']);
        $this->add_control('rvx_heading_panel_heading_style', ['type' => Controls_Manager::HEADING, 'label' => __('Heading', 'reviewx'), 'separator' => 'before']);
        $this->add_control('rvx_heading_color', ['label' => __('Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'selectors' => ['.woocommerce {{WRAPPER}} h2' => 'color: {{VALUE}}']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_content_heading_typography', 'label' => __('Typography', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .woocommerce-tabs h2']);
        $this->add_control('rvx_separator_panel_style', ['type' => Controls_Manager::DIVIDER]);
        $this->add_control('rvx_panel_border_width', ['label' => __('Border Width', 'reviewx'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['.woocommerce {{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; margin-top: -{{TOP}}{{UNIT}}']]);
        $this->add_control('rvx_panel_border_radius', ['label' => __('Border Radius', 'reviewx'), 'type' => Controls_Manager::DIMENSIONS, 'selectors' => ['.woocommerce {{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}', '.woocommerce {{WRAPPER}} .woocommerce-tabs ul.wc-tabs' => 'margin-left: {{TOP}}{{UNIT}}; margin-right: {{RIGHT}}{{UNIT}}']]);
        $this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'rvx_panel_box_shadow', 'selector' => '.woocommerce {{WRAPPER}} .woocommerce-tabs .woocommerce-Tabs-panel']);
        $this->end_controls_section();
        $this->graph();
        $this->reviewCard();
        $this->reviewForm();
        //        $this->frontSize();
        $this->filterOptions();
    }
    public function graph()
    {
        $this->start_controls_section('rvx_section_summary_style', ['label' => __('Review Summary', 'reviewx')]);
        $this->start_controls_tabs('graph_style');
        $this->add_control('rvx_template_average_rating_color', ['label' => __('Average Rating Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#424242', 'selectors' => ['{{WRAPPER}} #rvx-storefront-widget .rvx-average-rating' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_max_rating_color', ['label' => __('Max Rating Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#BDBDBD', 'selectors' => ['{{WRAPPER}} #rvx-storefront-widget .rvx-max-rating' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_badge_color', ['label' => __('Badge Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#4CAF50', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-rating-badge' => 'background-color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_budge_text_color', ['label' => __('Badge Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-rating-badge__text' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_average_rating_star_active_color', ['label' => __('Average Rating Star Active Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FCCE08', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-aggregation-summary__star-active' => 'fill: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-review__aggregation__summary__star-active-half-star' => 'stop-color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_average_rating_star_inactive_color', ['label' => __('Average Rating Star inactive Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => 'gray', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-review-form__star-inactive' => 'fill: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-review__aggregation__summary__star-inactive-half-star' => 'stop-color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_total_review_count_color', ['label' => __('Total Review Count Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#BDBDBD', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-total-review' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_summary_star_color', ['label' => __('Summary Star Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FCCE08', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-aggregation__row .rvx-aggregation__rating-icon path' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rvx_template_summary_progress_bar_active_color', ['label' => __('Summary Progress Bar Active Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => 'rgb(0, 67, 221)', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-aggregation__row .rvx-aggregation__progressbar .rvx-aggregation__progressbar-active' => 'background-color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_summary_progress_bar_inactive_color', ['label' => __('Summary Progress Bar Inactive Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#D9D9D9', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-aggregation__row .rvx-aggregation__progressbar .rvx-aggregation__progressbar-inactive' => 'background-color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_multi_criteria_title_color', ['label' => __('Multi Criteria Title Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#D9D9D9', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-aggregation-multicriteria .rvx-aggregation-multicriteria__name span' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_multi_criteria_progressbar_active_color', ['label' => __('Multi Criteria Progress Bar Active Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => 'rgb(0, 67, 221)', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-aggregation-multicriteria .rvx-aggregation__progressbar .rvx-aggregation__progressbar-active' => 'background-color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_multi_criteria_progressbar_inactive_color', ['label' => __('Multi Criteria Progress Bar Inactive Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#D9D9D9', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-aggregation-multicriteria .rvx-aggregation__progressbar .rvx-aggregation__progressbar-inactive' => 'background-color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_multi_criteria_star_color', ['label' => __('Multi Criteria Star Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FCCE08', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-aggregation-multicriteria .rvx-aggregation-multicriteria__total .rvx-aggregation__rating-icon path' => 'fill: {{VALUE}} !important']]);
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    public function reviewCard()
    {
        $this->start_controls_section('rvx_template_section_review_card_style', ['label' => __('Review Card', 'reviewx')]);
        $this->start_controls_tabs('rvx_template_tabs_review_card_style');
        $this->add_control('rvx_template_one_author_block_style', ['type' => Controls_Manager::HEADING, 'label' => __('Reviewer Information', 'reviewx'), 'separator' => 'after']);
        $this->add_control('rvx_template_reviewer_info_author_avatar_size', ['label' => __('Avatar Size', 'reviewx'), 'show_label' => \true, 'type' => Controls_Manager::IMAGE_DIMENSIONS, 'default' => ['width' => '50', 'height' => '50', 'unit' => 'px'], 'size_units' => ['px', 'em', '%'], 'selectors' => ['.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-review-user__avatar' => 'width: {{WIDTH}}{{UNIT}} !important; height: {{HEIGHT}}{{UNIT}} !important;', '
                    
                    .woocommerce {{WRAPPER}} #rvx-review-details .rvx-review-user__avatar' => 'width: {{WIDTH}}{{UNIT}} !important; height: {{HEIGHT}}{{UNIT}} !important;']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_template_reviewer_info_author_avatar_fallback_typography', 'label' => __('Avatar Fallback Typography', 'reviewx'), 'selector' => '
                .woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-review-user__avatar .rvx-review-user__avatar-fallback span,
                .woocommerce {{WRAPPER}} #rvx-review-details .rvx-review-user__avatar .rvx-review-user__avatar-fallback span
                ', 'fields_options' => ['font_size' => ['default' => ['unit' => 'px', 'size' => 16]]]]);
        $this->add_control('rvx_template_reviewer_info_author_avatar_text_color', ['label' => __('Avatar Fallback Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#BDBDBD', 'selectors' => ['.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-review-user__avatar .rvx-review-user__avatar-fallback span' => 'color: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-review-details .rvx-review-user__avatar .rvx-review-user__avatar-fallback span' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_reviewer_card_background_color_for_review', ['label' => __('Review Card Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#F5F5F5', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-review-wrapper .rvx-review-card' => 'background-color: {{VALUE}} !important;']]);
        $this->add_control('rvx_template_reviewer_info_author_name_text_color', ['label' => __('Name Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#373747', 'selectors' => ['.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-review-user .rvx-review-user__name' => 'color: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-review-details .rvx-review-user .rvx-review-user__name' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_reviewer_info_rating_star_active_color', ['label' => __('Rating Star Active Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#ECBD3F', 'selectors' => ['.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-reviewer__star-active' => 'fill: {{VALUE}} !important', '.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-reviewer__star-half.rvx-reviewer__star-active-half-star' => 'stop-color: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-review-details .rvx-reviewer__star-active' => 'fill: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-review-details .rvx-reviewer__star-half.rvx-reviewer__star-active-half-star' => 'stop-color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_reviewer_info_rating_star_inactive_color', ['label' => __('Rating Star Inactive Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#ECBD3F', 'selectors' => ['.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-reviewer__star-inactive' => 'fill: {{VALUE}} !important', '.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-reviewer__star-half rvx-reviewer__star-inactive-half-star' => 'fill: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-review-details__body .rvx-reviewer__star-inactive' => 'fill: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-review-details__body .rvx-reviewer__star-half.rvx-reviewer__star-inactive-half-star' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rvx_template_main_content_block_style', ['type' => Controls_Manager::HEADING, 'label' => __('Main Content', 'reviewx'), 'separator' => 'before']);
        $this->add_control('rvx_template_main_content_title_color', ['label' => __('Review Title Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#373747', 'selectors' => ['.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__title' => 'color: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-review-details .rvx-review-info .rvx-review-info__title' => 'color: {{VALUE}} !important']]);
        //        $this->add_group_control(
        //            Group_Control_Typography::get_type(),
        //            [
        //                'name' => 'rvx_template_main_content_review_title_typography',
        //                'label' => __('Review Title Typography', 'reviewx'),
        //                'selector' => '.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__title, .woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-details .rvx-review-info .rvx-review-info__title',
        //            ]
        //        );
        $this->add_control('rvx_template_main_content_review_description_text_color', ['label' => __('Review Description Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#9B9B9B', 'selectors' => ['.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__feedback' => 'color: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-review-details .rvx-review-info .rvx-review-info__feedback' => 'color: {{VALUE}} !important']]);
        //        $this->add_group_control(
        //            Group_Control_Typography::get_type(),
        //            [
        //                'name' => 'rvx_template_main_content_review_description_text_typography',
        //                'label' => __('Review Description Typography', 'reviewx'),
        //                'selector' => '.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__feedback,.woocommerce {{WRAPPER}} #rvx-review-details .rvx-review-info .rvx-review-info__feedback',
        //            ]
        //        );
        $this->add_control('rvx_template_main_content_review_description_review_date', ['label' => __('Review Date Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#757575', 'selectors' => ['.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__date' => 'color: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-review-details .rvx-review-info .rvx-review-info__date' => 'color: {{VALUE}} !important']]);
        //        $this->add_group_control(
        //            Group_Control_Typography::get_type(),
        //            [
        //                'name' => 'rvx_template_main_content_review_description_review_date_typography',
        //                'label' => __('Review Date Typography', 'reviewx'),
        //                'selector' => '.woocommerce {{WRAPPER}} .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__date, .woocommerce {{WRAPPER}} #rvx-review-details .rvx-review-info .rvx-review-info__date',
        //            ]
        //        );
        $this->add_control('rvx_template_main_content_footer_action_helpful_message_text_color', ['label' => __('Was This Helpful Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#333', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-review-card .rvx-review-footer .rvx-review-footer__text' => 'color: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-details .rvx-review-footer .rvx-review-footer__text' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_main_content_footer_like', ['label' => __('Review Like Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#E0E0E0', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-review-card .rvx-review-footer__thumbs--like-icon path' => 'fill: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-details .rvx-review-footer__thumbs--like-icon path' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rvx_template_card_load_more', ['label' => __('Load More Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget button' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_template_card_load_more_text_color', ['label' => __('Load More Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#424242', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget button' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_card_load_more_background_hover_color', ['label' => __('Load More Hover Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#D5D5D5', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget button:hover' => 'background-color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_card_load_more_hover_text_color', ['label' => __('Load More Text Hover Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#424242', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget button:hover' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_main_content_footer_dislike', ['label' => __('Review Dislike', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#E0E0E0', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-review-card .rvx-review-footer__thumbs--dislike-icon path' => 'fill: {{VALUE}} !important', '.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-details .rvx-review-footer__thumbs--dislike-icon path' => 'fill: {{VALUE}} !important']]);
        /*********************************
         *    Review Description End
         * *********************************/
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    public function filterOptions()
    {
        $this->start_controls_section('rvx_template_filter_section', ['label' => __('Filter Options', 'reviewx')]);
        $this->add_control('rvx_template_write_review_action_bg_color', ['label' => __('Write Review Action Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#387CF7', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-write__button' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_template_write_review_action_text_color', ['label' => __('Write Review Action Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#fff', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-write__button' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_filter_text_color', ['label' => __('Filter Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#424242', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__button' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_filter_text_button_background_color', ['label' => __('Filter Button Background color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#F5F5F5', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__button' => 'background: {{VALUE}} !important']]);
        //        $this->add_group_control(
        //            Group_Control_Typography::get_type(),
        //            [
        //                'name' => 'rvx_filter_button_font_size',
        //                'label' => __('Filter Button Font Size', 'reviewx'),
        //                'selector' => '.woocommerce {{WRAPPER}} #rvx-storefront-widget .rvx-review-filter__button',
        //                'fields_options' => [
        //                    'font_size' => [
        //                        'default' => [
        //                            'unit' => 'px',
        //                            'size' => 16, // Default font size set to 16px
        //                        ],
        //                    ],
        //                ],
        //            ]
        //        );
        $this->add_control('rvx_filter_dropdown_background_color', ['label' => __('Filter Dropdown Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter-wrapper,
                    .woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter-wrapper .rvx-review-filter__wrapper-inner,
                    .woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter-wrapper .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer
                    ' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_template_filter_by_text_color', ['label' => __('Dropdown Filter By Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#6B707A', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__title' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_filter_options_text_color', ['label' => __('Dropdown Filter Options Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#6B707A', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__rating .rvx-review-filter-wrapper__rating--text,
                    
                    .woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__rating .rvx-review-filter-wrapper__rating-wrapper .rvx-review-filter-wrapper__rating-inner .rvx-review-filter__wrapper__rating--radio-group__option-label,
                    
                    .woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__attachment .rvx-review-filter-wrapper__attachment--text,
                    
                    .woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__attachment .rvx-review-filter-wrapper__attachment-wrapper .rvx-review-filter-wrapper__attachment-inner .rvx-review-filter__wrapper__attachment--radio-group__option-label
                    
                    ' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_filter_options_icon_color', ['label' => __('Dropdown Filter Option Icon Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#6B707A', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__rating .rvx-review-filter-wrapper__rating-inner--icon,
                    
                    .woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__attachment .rvx-review-filter-wrapper__attachment-inner--icon
                    ' => 'color: {{VALUE}} !important']]);
        //        $this->add_group_control(
        //            Group_Control_Typography::get_type(),
        //            [
        //                'name' => 'rvx_filter_by_font_size',
        //                'label' => __('Dropdown Filter By Font Size', 'reviewx'),
        //                'selector' => '.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter-wrapper__title',
        //                'fields_options' => [
        //                    'font_size' => [
        //                        'default' => [
        //                            'unit' => 'px',
        //                            'size' => 16, // Default font size set to 16px
        //                        ],
        //                    ],
        //                ],
        //            ]
        //        );
        $this->add_control('rvx_template_filter_reset_button_text_color', ['label' => __('Filter Reset Button Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#383239', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter-wrapper__footer button' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_filter_reset_button_background_color', ['label' => __('Filter Reset Button Background', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#F5F5F5', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter-wrapper__footer button' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_template_filter_reset_button_border_radius', ['label' => __('Filter Reset Button Border Radius', 'reviewx'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-filter-wrapper__footer button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']]);
        $this->add_control('rvx_template_sort_by_button_text_color', ['label' => __('Sort By Button Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#424242', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-sort__button' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_sort_by_button_bg_color', ['label' => __('Sort By Button Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#fff', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-sort__button' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_template_sort_by_dropdown_text_color', ['label' => __('Sort By Dropdown Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#424242', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-sort-wrapper .rvx-review-sort-wrapper__outer .rvx-review-sort-wrapper__inner .rvx-review-sort__wrapper--radio-group__option-label' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_sort_by_dropdown_bg_color', ['label' => __('Sort By Dropdown Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#fff', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-filter .rvx-review-sort-wrapper' => 'background: {{VALUE}} !important']]);
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    public function reviewForm()
    {
        $this->start_controls_section('rvx_template_review_form_section_style', ['label' => __('Review Form', 'reviewx')]);
        // Form Text: Write a Review
        $this->add_control('rvx_template_review_form_text_write_a_review', ['label' => __('Write a Review', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Write a Review', 'selectors' => ['{{WRAPPER}} .rvx-review-form__title' => 'textContent: {{VALUE}};']]);
        // Form Text: Rating Title (Text)
        $this->add_control('rvx_template_review_form_text_rating_star_title', ['label' => __('Rating (Text)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Rating', 'selectors' => ['{{WRAPPER}} .rvx-review-form__rating--name' => 'textContent: {{VALUE}};']]);
        // Form Text: Review Title (Text)
        $this->add_control('rvx_template_review_form_text_review_title', ['label' => __('Review Title (Text)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Review Title', 'selectors' => ['{{WRAPPER}} .rvx-review-form__title--name' => 'textContent: {{VALUE}};']]);
        // Form Text: Review Title (Placeholder)
        $this->add_control('rvx_template_review_form_placeholder_review_title', ['label' => __('Review Title (Placeholder)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Write Review Title', 'selectors' => ['{{WRAPPER}} .rvx-review-form__title--input' => 'textContent: {{VALUE}};']]);
        // Form Text: Review Description (Text)
        $this->add_control('rvx_template_review_form_text_review_description', ['label' => __('Description (Text)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Description', 'selectors' => ['{{WRAPPER}} .rvx-review-form__description--title' => 'textContent: {{VALUE}};']]);
        // Form Text: Review Description (Placeholder)
        $this->add_control('rvx_template_review_form_placeholder_review_description', ['label' => __('Description (Placeholder)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Write your description here', 'selectors' => ['{{WRAPPER}} .rvx-review-form__title--textarea' => 'textContent: {{VALUE}};']]);
        // Form Text: Full Name (Text)
        $this->add_control('rvx_template_review_form_text_full_name', ['label' => __('Full Name (Text)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Full Name', 'selectors' => ['{{WRAPPER}} .rvx-review-form__description--title' => 'textContent: {{VALUE}};']]);
        // Form Text: Full Name (Placeholder)
        $this->add_control('rvx_template_review_form_placeholder_full_name', ['label' => __('Full Name (Placeholder)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Full Name', 'selectors' => ['{{WRAPPER}} .rvx-review-form__title--textarea' => 'textContent: {{VALUE}};']]);
        // Form Text: Email Address (Text)
        $this->add_control('rvx_template_review_form_text_email_name', ['label' => __('Email Address (Text)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Email Address', 'selectors' => ['{{WRAPPER}} .rvx-review-form__description--title' => 'textContent: {{VALUE}};']]);
        // Form Text: Email Address (Placeholder)
        $this->add_control('rvx_template_review_form_placeholder_email_name', ['label' => __('Email Address (Placeholder)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Email Address', 'selectors' => ['{{WRAPPER}} .rvx-review-form__title--textarea' => 'textContent: {{VALUE}};']]);
        // Form Text: Attachment (Text)
        $this->add_control('rvx_template_review_form_text_attachment_title', ['label' => __('Attachment (Text)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Attachment', 'selectors' => ['{{WRAPPER}} .rvx-review-form__attachment--name' => 'textContent: {{VALUE}};']]);
        // Form Text: Upload Photo / Video (Placeholder)
        $this->add_control('rvx_template_review_form_placeholder_upload_photo', ['label' => __('Upload Photo / video (Placeholder)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Upload Photo / Video', 'selectors' => ['{{WRAPPER}} .rvx-review-form__attachment--upload--text' => 'textContent: {{VALUE}};']]);
        // Form Text: Mark as Anonymous (Text)
        $this->add_control('rvx_template_review_form_text_mark_as_anonymous', ['label' => __('Mark as Anonymous (Text)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Mark as Anonymous', 'selectors' => ['{{WRAPPER}} .rvx-review-form__mark-anonymous' => 'textContent: {{VALUE}};']]);
        // Form Text: Recommended? (Text)
        $this->add_control('rvx_template_review_form_text_recommended_title', ['label' => __('Recommended? (Text)', 'reviewx'), 'type' => Controls_Manager::TEXT, 'default' => 'Recommendation?', 'selectors' => ['{{WRAPPER}} .rvx-review-form__recommended--name' => 'textContent: {{VALUE}};']]);
        $this->add_control('rvx_template_review_form_background_color', ['label' => __('Form Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#F5F5F5', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-storefront-widget #rvx-review-form__wrapper' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_title_color', ['label' => __('Form Title Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#424242', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__title' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_border_color', ['label' => __('Form Border Line Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#E0E0E0', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__line' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_product_image_size', ['label' => __('Product Image Size', 'reviewx'), 'type' => Controls_Manager::IMAGE_DIMENSIONS, 'show_label' => \true, 'default' => ['width' => '64', 'height' => '64', 'unit' => 'px'], 'size_units' => ['px', 'em', '%'], 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__product .rvx-review-form__product--image' => 'width: {{WIDTH}}{{UNIT}} !important; height: {{HEIGHT}}{{UNIT}} !important;']]);
        //        $this->add_control(
        //            'rvx_hide_product_image',
        //            [
        //                'label' => __('Hide Product Image', 'reviewx'),
        //                'type' => Controls_Manager::HIDDEN,
        //                'default' => 'block',
        //                'selectors' => [
        //                    '.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__inner .rvx-review-form__product .rvx-review-form__product' => 'display: {{VALUE}} !important',
        //                ],
        //            ]
        //        );
        //
        $this->add_control('rvx_template_review_form_product_title_color', ['label' => __('Product Title Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#424242', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__product .rvx-review-form__product--title' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_rating_star_active_color', ['label' => __('Rating Active Star Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FCCE08', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__rating .rvx-review-form__star-active' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_rating_star_inactive_color', ['label' => __('Rating Inactive Star Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FCCE08', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__rating .rvx-review-form__star-inactive' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_multi_criteria_star_active_color', ['label' => __('Multi Criteria Active Star Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FCCE08', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__multicriteria .rvx-review-form__star-active' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_multi_criteria_star_inactive_color', ['label' => __('Multi Criteria Inactive Star Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#757575', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__multicriteria .rvx-review-form__star-inactive' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_label_color', ['label' => __('Review Form Label Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#424242', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__title .rvx-review-form__title--name,
                    
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__description .rvx-review-form__description--title,
                    
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__rating .rvx-review-form__rating--name,
                    
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__multicriteria .rvx-review-form__multicriteria--name,
                    
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__user .rvx-review-form__user--name,
                    
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__email .rvx-review-form__email--name,
                    
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__attachment .rvx-review-form__attachment--name,
                    
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__attachment--inner .rvx-review-form__mark-anonymous,
                    
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__recommended .rvx-review-form__recommended--name,
                    
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__recommended label
                    ' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_review_title_input_placeholder_color', ['label' => __('Review Form Input Placeholder Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#BDBDBD', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper input::placeholder,
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper textarea::placeholder
                    ' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_input_background_color', ['label' => __('Review Form Input Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#fff', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper input,
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper textarea
                    ' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form__attachments_icon_color', ['label' => __('Review Attachment Icon Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#9E9E9E', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__attachment .rvx-review-form__attachment--inner .rvx-review-form__attachment--upload--icon' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form__attachments_text_color', ['label' => __('Review Attachment Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#9E9E9E', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__attachment .rvx-review-form__attachment--inner .rvx-review-form__attachment--upload--count,
                    .woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__attachment .rvx-review-form__attachment--inner .rvx-review-form__attachment--upload--text' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form__attachments_background_color', ['label' => __('Review Attachment Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#EEEEEE', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__attachment .rvx-review-form__attachment--inner .rvx-review-form__attachment--upload' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_submit_button_bg_color', ['label' => __('Submit Button Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#2f4fff', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"],
					.woocommerce {{WRAPPER}} .woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"]:focus' => 'background-color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_submit_button_text_color', ['label' => __('Submit Button Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#fff', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"],
					.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"]:focus ' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_submit_button_hover_background_color', ['label' => __('Submit Button Hover Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#fff', 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"],
					.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"]:hover ' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_review_form_submit_button_border_radius', ['label' => __('Submit Button Border Radius', 'reviewx'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']]);
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    public function frontSize()
    {
        $this->start_controls_section('rvx_template_font_section', ['label' => __('Font size', 'reviewx')]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_template_front_size_text_typography', 'label' => __('Review Criteria Typography', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .rvx-review-form__multicriteria--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_criteria_label_typography', 'label' => __('Criteria Label Typography', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .rvx-review-form__multicriteria--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_criteria_value_typography', 'label' => __('Criteria Value Typography', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .rvx-aggregation-multicriteria__row-inner .rvx-review-form__multicriteria--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_title_lable_typography', 'label' => __('Title lable', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} #rvx-review-form__wrapper .rvx-review-form__title--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_title_value_typography', 'label' => __('Title Value', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .rvx-review-info .rvx-review-info__title']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_description_label_typography', 'label' => __('Description label', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .rvx-review-form__description .rvx-review-form__title--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_description_value_typography', 'label' => __('Description Value', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .rvx-review-info .rvx-review-info__feedback']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_attachments_typography', 'label' => __('Attachment', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .rvx-review-form__attachment--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_recommended_typography', 'label' => __('Recommended', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .rvx-review-form__recommended--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_anonymous_typography', 'label' => __('Mark as Anonymous', 'reviewx'), 'selector' => '.woocommerce {{WRAPPER}} .rvx-review-form__mark-anonymous']);
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    /**
     * Template One Style
     * @return void
     */
    /**
     * Render
     * @return void
     */
    protected function render()
    {
        global $builderElementorSetting;
        $settings = $this->get_settings_for_display();
        $builderElementorSetting = [];
        // Define the keys to be added if they have a value
        $keys = ['write_a_review' => 'rvx_template_review_form_text_write_a_review', 'text_rating_star_title' => 'rvx_template_review_form_text_rating_star_title', 'text_review_title' => 'rvx_template_review_form_text_review_title', 'placeholder_review_title' => 'rvx_template_review_form_placeholder_review_title', 'text_review_description' => 'rvx_template_review_form_text_review_description', 'placeholder_review_description' => 'rvx_template_review_form_placeholder_review_description', 'text_full_name' => 'rvx_template_review_form_text_full_name', 'placeholder_full_name' => 'rvx_template_review_form_placeholder_full_name', 'text_email_name' => 'rvx_template_review_form_text_email_name', 'placeholder_email_name' => 'rvx_template_review_form_placeholder_email_name', 'text_attachment_title' => 'rvx_template_review_form_text_attachment_title', 'placeholder_upload_photo' => 'rvx_template_review_form_placeholder_upload_photo', 'text_mark_as_anonymous' => 'rvx_template_review_form_text_mark_as_anonymous', 'text_recommended_title' => 'rvx_template_review_form_text_recommended_title'];
        // Loop through each key and add it to the array only if it has a non-empty value
        foreach ($keys as $key => $settingKey) {
            if (!empty($settings[$settingKey])) {
                $builderElementorSetting[$key] = $settings[$settingKey];
            }
        }
        if (\class_exists('WooCommerce')) {
            global $product;
            $product = wc_get_product();
            if (empty($product)) {
                echo '<h3>' . __('This widget only works for the product page. In order to achieve, follow the steps: this  Dashboard >  Template  > Theme Builder > Add New > Choose Template Type \'Single Product\' > Create Template', 'reviewx') . '</h3>';
                return;
            }
            setup_postdata($product->get_id());
            \call_user_func('comments_template', 'reviews');
        }
    }
    /**
     *
     */
    public function render_plain_content()
    {
    }
}

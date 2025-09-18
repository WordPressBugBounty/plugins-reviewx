<?php

namespace Rvx;

// namespace ReviewX\Elementor\Elements;
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
use ReviewX\Constants\Reviewx;
/**
 * Class Review
 * @package ReviewX\Elementor\Elements
 */
class Cpt_Widgets extends Widget_Base
{
    /**
     * @return string
     */
    public function get_name()
    {
        return 'rx-cpt-widget';
    }
    /**
     * @return string|void
     */
    public function get_title()
    {
        return __('ReviewX CPT Review', 'reviewx');
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
        return ['reviewx', 'cpt review', 'cpt', 'comment', 'review', 'addons', 'ea', 'essential addons'];
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
        $this->graph();
        $this->reviewItem();
        $this->form();
        $this->frontSize();
        $this->filterOptions();
    }
    public function graph()
    {
        $this->start_controls_section('rx_section_graph_style', ['label' => __('Graph of Review Criteria', 'reviewx')]);
        $this->start_controls_tabs('graph_style');
        $this->add_control('rx_summary_progress_bar_box_border_color', ['label' => __('Rating Out Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#424242', 'selectors' => ['  #rvx-storefront-widget .rvx-rating-out-of' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_rating_color', ['label' => __('Rating Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#6C7075', 'selectors' => ['  #rvx-storefront-widget .rvx-rating' => 'color: {{VALUE}} !important']]);
        $this->add_control('rx_template_badge_color', ['label' => __('Badge Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#5FC65F', 'selectors' => ['  #rvx-storefront-widget .rvx-rating-badge' => 'background-color: {{VALUE}} !important']]);
        $this->add_control('rx_template_text_color', ['label' => __('Badge Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => ['   #rvx-storefront-widget .rvx-rating-badge__text' => 'color: {{VALUE}} !important']]);
        $this->add_control('rx_template_summary_star_color', ['label' => __('Summary Star Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FBD045', 'selectors' => ['   #rvx-storefront-widget .rvx-aggregation__rating .rvx-aggregation__rating-icon path' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rx_template_summary_criteria_star_color', ['label' => __('Summary Criteria Star Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FBD045', 'selectors' => ['   #rvx-storefront-widget .rvx-aggregation-multicriteria__total .rvx-aggregation-multicriteria__start-icon path' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rx_template_aggeregation_criteria_progressbar_color', ['label' => __('Aggregation Multicriteria Bar', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FBD045', 'selectors' => ['   #rvx-storefront-widget .rvx-aggregation__progressbar .rvx-aggregation__progressbar-active' => 'background: {{VALUE}} !important']]);
        $this->add_control('rx_template_progressbar_active_color', ['label' => __('Progressbar Active Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FBD045', 'selectors' => ['   #rvx-storefront-widget .rvx-aggregation__progressbar .rvx-aggregation__progressbar-active' => 'background: {{VALUE}} !important']]);
        $this->add_control('rx_template_total_review_color', ['label' => __('Total Reviews Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#717C71', 'selectors' => ['   #rvx-storefront-widget .rvx-total-review' => 'color: {{VALUE}} !important']]);
        $this->add_control('rx_template_critriya_color', ['label' => __('Total Criteria Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#6d6d6d', 'selectors' => ['   #rvx-storefront-widget .rvx-aggregation-multicriteria__name' => 'color: {{VALUE}} !important']]);
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    public function reviewItem()
    {
        $this->start_controls_section('rx_template_one_section_review_style', ['label' => __('Review Item', 'reviewx')]);
        $this->start_controls_tabs('template_one_review_style');
        $this->add_control('rx_template_one_author_block_style', ['type' => Controls_Manager::HEADING, 'label' => __('Reviewer Information', 'reviewx'), 'separator' => 'after']);
        $this->add_control('rx_template_one_author_color', ['label' => __('Reviewer Name Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#373747', 'selectors' => ['   .rvx-review-user .rvx-review-user__name' => 'color: {{VALUE}} !important']]);
        $this->add_control('rx_template_one_main_content_block_style', ['type' => Controls_Manager::HEADING, 'label' => __('Main Content', 'reviewx'), 'separator' => 'before']);
        $this->add_control('rx_template_one_title_color', ['label' => __('Review Title Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#373747', 'selectors' => ['   .rvx-review-info .rvx-review-info__title' => 'color: {{VALUE}} !important']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_template_one_title_typography', 'label' => __('Review Title Typography', 'reviewx'), 'selector' => '   .rvx-review-info .rvx-review-info__title']);
        $this->add_control('rx_template_one_text_color', ['label' => __('Review Comments Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#9B9B9B', 'selectors' => ['   .rvx-review-info .rvx-review-info__feedback' => 'color: {{VALUE}} !important']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_template_one_text_typography', 'label' => __('Review Comments Typography', 'reviewx'), 'selector' => '   .rvx-review-info .rvx-review-info__feedback']);
        /*********************************
         * 	Review Comments End
         * *********************************/
        $this->add_control('rx_template_one_text_block_style', ['type' => Controls_Manager::HEADING, 'label' => __('Meta Information', 'reviewx'), 'separator' => 'before']);
        $this->add_control('rvx_write_review_bg', ['label' => __('Write Review', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#387CF7', 'selectors' => ['   #rvx-storefront-widget .rvx-review-write__button' => 'background-color : {{VALUE}} !important']]);
        $this->add_control('rvx_write_review_text', ['label' => __('Write Review Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => ['   #rvx-storefront-widget .rvx-review-write__button' => 'color : {{VALUE}} !important']]);
        $this->add_control('rx_template_one_date_icon_color', ['label' => __('Reviewed Date Icon Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#707070', 'selectors' => ['   .rvx-review-info .rvx-review-info__date' => 'color: {{VALUE}} !important']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_template_one_date_typography', 'label' => __('Reviewed Date Typography', 'reviewx'), 'selector' => '   .rvx-review-info .rvx-review-info__date']);
        $this->add_control('rvx_was_his_helpful_color', ['label' => __('Was this helpful', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#333', 'selectors' => ['   #rvx-storefront-widget .rvx-review-footer .rvx-review-footer__text' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_like', ['label' => __('Review Item Like', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#E0E0E0', 'selectors' => ['   #rvx-storefront-widget .rvx-review-footer__thumbs--like-icon path' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rvx_template_dislike', ['label' => __('Review Item Disike', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#E0E0E0', 'selectors' => ['   #rvx-storefront-widget .rvx-review-footer__thumbs--dislike-icon path' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rvx_template_username', ['label' => __('User Name Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => ['   #rvx-storefront-widget .rvx-review-user .rvx-review-user__avatar' => 'background-color: {{VALUE}} !important']]);
        $this->add_control('rvx_template_username_feedback', ['label' => __('User Name Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => ['   #rvx-storefront-widget .rvx-review-user .rvx-review-user__avatar-fallback' => 'color: {{VALUE}} !important']]);
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    public function form()
    {
        $this->start_controls_section('rx_template_one_section_form_style', ['label' => __('Review Form', 'reviewx')]);
        $this->add_control('rvx_form_background_color', ['label' => __('Form Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#F5F5F5', 'selectors' => ['   #rvx-review-form__wrapper' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_criteria_star_color', ['label' => __('Criteria star color active', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#ECBD3F', 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__star-active' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rvx_criteria_inactive_star_color', ['label' => __('Criteria star color inactive', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#808080', 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__star-inactive' => 'fill: {{VALUE}} !important']]);
        $this->add_control('rvx_criteria_star_half_color_active', ['label' => __('Criteria star color inactive', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#808080', 'selectors' => [' #rvx-review-form__wrapper .rvx-review-form__star-active-half stop:first-child' => 'stop-color: {{VALUE}} !important']]);
        $this->add_control('rvx_criteria_half_star_color_inactive', ['label' => __('Criteria star color inactive', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#808080', 'selectors' => [' #rvx-review-form__wrapper .rvx-review-form__star-active-half stop:last-child' => 'stop-color: {{VALUE}} !important']]);
        $this->add_control('rvx_placeholder_text_color', ['label' => __('Placeholder text color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#BDBDBD', 'selectors' => ['   #rvx-review-form__wrapper input::placeholder' => 'color: {{VALUE}} !important', '   #rvx-review-form__wrapper textarea::placeholder' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_product_image_radius', ['label' => __('Product image radius', 'reviewx'), 'type' => Controls_Manager::IMAGE_DIMENSIONS, 'default' => ['width' => '', 'height' => '', 'unit' => 'px'], 'size_units' => ['px', 'em', '%'], 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__product img' => 'width: {{WIDTH}}{{UNIT}} !important; height: {{HEIGHT}}{{UNIT}} !important;']]);
        $this->add_control('rvx_placeholder_text_color_one', ['label' => __('Hide product image', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#F5F5F5', 'selectors' => ['   #rvx-review-form__wrapper' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_placeholder_text_color_two', ['label' => __('Product image radius', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#F5F5F5', 'selectors' => ['   #rvx-review-form__wrapper' => 'background: {{VALUE}} !important']]);
        $this->add_control('rx_product_title_color', ['label' => __('Product Title Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#1a1a1a', 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__product--title' => 'color: {{VALUE}} !important']]);
        $this->add_control('rx_template_one_form_separator_one', ['type' => Controls_Manager::DIVIDER]);
        $this->add_control('rvx_review_form__title_namemulti', ['label' => __('Criteria Title Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#1a1a1a', 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__multicriteria--name' => 'color: {{VALUE}} !important']]);
        //rvx-review-form__title--name
        $this->add_control('rvx_review_form__title_name', ['label' => __('Review Input Title Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#3797FF', 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__title--name' => 'color: {{VALUE}} !important']]);
        $this->add_control('rx_template_one_form_separator_two', ['type' => Controls_Manager::DIVIDER]);
        $this->add_control('rvx_review_form__description', ['label' => __('Review Description Title', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#6d6d6d', 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__description .rvx-review-form__title--name' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_review_form__attachments', ['label' => __('Review Attachment Title', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#6d6d6d', 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__attachment .rvx-review-form__attachment--name' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_review_form__recommended', ['label' => __('Review Recommended Title', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#6d6d6d', 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__recommended .rvx-review-form__recommended--name' => 'color: {{VALUE}} !important']]);
        $this->add_control('rx_template_one_form_separator_three', ['type' => Controls_Manager::DIVIDER]);
        $this->add_control('rx_template_one_form_submit_button_text_color', ['label' => __('Submit Button Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#fff', 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"],
					   #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"]:focus ' => 'color: {{VALUE}} !important']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_template_one_form_submit_button_text_typography', 'label' => __('Submit Button Text Typography', 'reviewx'), 'selector' => '   #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"],
				   #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"]:focus']);
        $this->add_control('rx_template_one_form_submit_button_bg_color', ['label' => __('Submit Button Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#2f4fff', 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"],
					      #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"]:focus' => 'background-color: {{VALUE}} !important']]);
        $this->add_control('rx_template_one_form_submit_button_border_radius', ['label' => __('Submit Button Border Radius', 'reviewx'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['   #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']]);
        $this->add_control('rx_anonymous_submit_text_color', ['label' => __('Anonymous Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#6D6D6D', 'selectors' => ['   #rvx-storefront-widget .rvx-review-form__mark-anonymous' => 'color: {{VALUE}} !important;']]);
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    public function frontSize()
    {
        $this->start_controls_section('rx_template_font_section', ['label' => __('Front size', 'reviewx')]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_template_front_size_text_typography', 'label' => __('Review Criteria Typography', 'reviewx'), 'selector' => '   .rvx-review-form__multicriteria--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_criteria_label_typography', 'label' => __('Criteria Label Typography', 'reviewx'), 'selector' => '   .rvx-review-form__multicriteria--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_criteria_value_typography', 'label' => __('Criteria Value Typography', 'reviewx'), 'selector' => '   .rvx-aggregation-multicriteria__row-inner .rvx-review-form__multicriteria--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_title_lable_typography', 'label' => __('Title lable', 'reviewx'), 'selector' => '   #rvx-review-form__wrapper .rvx-review-form__title--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_title_value_typography', 'label' => __('Title Value', 'reviewx'), 'selector' => '   .rvx-review-info .rvx-review-info__title']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_description_label_typography', 'label' => __('Description label', 'reviewx'), 'selector' => '   .rvx-review-form__description .rvx-review-form__title--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_description_value_typography', 'label' => __('Description Value', 'reviewx'), 'selector' => '   .rvx-review-info .rvx-review-info__feedback']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_attachments_typography', 'label' => __('Attachment', 'reviewx'), 'selector' => '   .rvx-review-form__attachment--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_recommended_typography', 'label' => __('Recommended', 'reviewx'), 'selector' => '   .rvx-review-form__recommended--name']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rx_anonymous_typography', 'label' => __('Mark as Anonymous', 'reviewx'), 'selector' => '   .rvx-review-form__mark-anonymousk']);
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    public function filterOptions()
    {
        $this->start_controls_section('rx_template_filter_section', ['label' => __('Filter Options', 'reviewx')]);
        $this->add_control('rvx_filter_text_color', ['label' => __('Filter Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#000000', 'selectors' => [' #rvx-storefront-widget .rvx-review-filter__button' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_filter_text_button_background_color', ['label' => __('Filter Button Background color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#F5F5F5', 'selectors' => [' #rvx-storefront-widget .rvx-review-filter__button' => 'background: {{VALUE}} !important']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_filter_button_font_size', 'label' => __('Filter Button Font Size', 'reviewx'), 'selector' => ' #rvx-storefront-widget .rvx-review-filter__button', 'fields_options' => ['font_size' => ['default' => ['unit' => 'px', 'size' => 16]]]]);
        $this->add_control('rvx_filter_by_text_color', ['label' => __('Filter By Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#6B707A', 'selectors' => [' #rvx-storefront-widget .rvx-review-filter-wrapper__title' => 'color: {{VALUE}} !important']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'rvx_filter_by_font_size', 'label' => __('Filter By Font Size', 'reviewx'), 'selector' => ' #rvx-storefront-widget .rvx-review-filter-wrapper__title', 'fields_options' => ['font_size' => ['default' => ['unit' => 'px', 'size' => 16]]]]);
        $this->add_control('rvx_filter_dropdown_background_color', ['label' => __('Dropdown Background Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => [' .rvx-review-filter-wrapper' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_filter_reset_filters_text_color', ['label' => __('Reset Filters Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#383239', 'selectors' => [' .rvx-review-filter-wrapper__footer button' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_filter_filter_button_background', ['label' => __('Filter Button Background', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#F5F5F5', 'selectors' => [' .rvx-review-filter-wrapper__footer button' => 'background: {{VALUE}} !important']]);
        $this->add_control('rvx_filter_button_border_radius', ['label' => __('Button Border Radius', 'reviewx'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => [' .rvx-review-filter-wrapper__footer button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']]);
        $this->add_control('rvx_filter_dropdown_text_color', ['label' => __('Filter Dropdown Text Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#F5F5F5', 'selectors' => [' .rvx-review-filter-wrapper' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_filter_main_short_dropdown_text_color', ['label' => __('Short By Dropdown Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#000000', 'selectors' => [' .rvx-review-filter-wrapper__outer' => 'color: {{VALUE}} !important']]);
        $this->add_control('rvx_filter_main_short_background_color', ['label' => __('Short By Dropdown BG Color', 'reviewx'), 'type' => Controls_Manager::COLOR, 'default' => '#F5F5F5', 'selectors' => [' .rvx-review-filter-wrapper__outer' => 'background: {{VALUE}} !important']]);
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    /**
     * Render
     * @return void
     */
    protected function render()
    {
        global $post;
        $post = get_post();
        if ($post->post_type == 'product') {
            echo '<h3>' . __('This widget only works for the post page. In order to achieve, follow the steps: this  Dashboard >  Template  > Theme Builder > Add New > Choose Template Type \'Single Post\' > Create Template', 'reviewx') . '</h3>';
            return;
        }
        $rx_template_type = $this->get_settings_for_display();
        setup_postdata($post->ID);
        \call_user_func('comments_template', 'reviews');
    }
    /**
     *
     */
    public function render_plain_content()
    {
    }
}
/**
 * Class Review
 * @package ReviewX\Elementor\Elements
 */
\class_alias('Rvx\\Cpt_Widgets', 'Cpt_Widgets', \false);

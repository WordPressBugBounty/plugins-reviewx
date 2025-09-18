<?php

namespace Rvx;

use Rvx\RvxDivi\includes\modules\RvxTabs\DiviDesignTrait;
class RVX_Builder_Module_Woocommerce_Reviews extends \ET_Builder_Module_Tabs
{
    /**
     * Define WooCommerce Tabs property.
     */
    use DiviDesignTrait;
    /**
     * A stack of the current active theme builder layout post type.
     *
     * @var string[]
     */
    public function init()
    {
        // Inherit tabs module property.
        parent::init();
        // Define WooCommerce Tabs module property; overwriting inherited property.
        $this->name = esc_html__('ReviewX Woo Reviews', 'reviewx');
        $this->plural = esc_html__('ReviewX Woo Reviews', 'reviewx');
        $this->slug = 'et_pb_wc_review_for_ReviewX';
        $this->vb_support = 'on';
        $this->main_css_element = '%%order_class%%.et_pb_toggle';
        /*
         * Set property for holding rendering data so the data rendering via
         * ET_Builder_Module_Woocommerce_Tabs::get_tabs() is only need to be done once.
         */
        $this->rendered_tabs_data = array();
        // Remove module item.
        $this->child_slug = 'rvx_et_pb_reviews';
        $this->child_item_text = null;
        // Set WooCommerce Tabs specific toggle / options group.
        $this->settings_modal_toggles['general']['toggles']['main_content'] = array('title' => et_builder_i18n('Content'), 'priority' => 10);
        $this->advanced_fields['fonts']['tab']['font_size'] = array('default' => '14px');
        $this->advanced_fields['fonts']['tab']['line_height'] = array('default' => '1.7em');
        $this->advanced_fields = $this->getAdvance();
        $this->help_videos = array(array('id' => '7X03vBPYJ1o', 'name' => esc_html__('Divi WooCommerce Modules', 'reviewx')));
    }
    /**
     * Get product all possible tabs data
     *
     * @since 3.29
     * @since 4.4.2   Fix to include Custom tabs.
     *
     * @global WP_Post    $post    WordPress Post.
     * @global WC_Product $product WooCommerce Product.
     *
     * @return array
     */
    public function get_product_tabs()
    {
        static $tabs = null;
        if (!\is_null($tabs)) {
            return $tabs;
        }
        global $post, $product;
        // Save existing $post and $product global.
        $original_post = $post;
        $original_product = $product;
        $post_id = 'product' === $this->get_post_type() ? ET_Builder_Element::get_current_post_id() : \ET_Builder_Module_Helper_Woocommerce_Modules::get_product_id('latest');
        // Overwriting global $post is necessary as WooCommerce relies on it.
        $post = get_post($post_id);
        $product = wc_get_product($post_id);
        /*
         * Get relevant product tabs data. Product tabs hooks use global based conditional
         * for adding / removing product tabs data via filter hoook callback, hence the
         * need to overwrite the global for determining product tabs data
         */
        $tabs = \is_object($product) ? apply_filters('woocommerce_product_tabs', array()) : \ET_Builder_Module_Helper_Woocommerce_Modules::get_default_product_tabs();
        // Reset $post and $product global.
        $post = $original_post;
        $product = $original_product;
        /*
         * Always return all possible tabs
         */
        return $tabs;
    }
    /**
     * Get product tabs options; product data formatted for checkbox control's options
     *
     * @since 3.29
     *
     * @return array
     */
    public function get_tab_options()
    {
        $tabs = $this->get_product_tabs();
        $options = array();
        foreach ($tabs as $name => $tab) {
            if (!isset($tab['title'])) {
                continue;
            }
            $options[$name] = array('value' => $name, 'label' => 'reviews' === $name ? esc_html__('Reviews', 'reviewx') : esc_html($tab['title']));
        }
        return $options;
    }
    /**
     * Get product tabs default based on product tabs options
     *
     * @since 3.29
     *
     * @return string
     */
    public function get_tab_defaults()
    {
        return \implode('|', \array_keys($this->get_product_tabs()));
    }
    /**
     * Define Woo Tabs fields
     *
     * @since 3.29
     *
     * @return array
     */
    public function get_fields()
    {
        return \array_merge(parent::get_fields(), [
            'product' => \ET_Builder_Module_Helper_Woocommerce_Modules::get_field('product', array('default' => \ET_Builder_Module_Helper_Woocommerce_Modules::get_product_default(), 'computed_affects' => array('__tabs', 'include_tabs'))),
            'include_tabs' => array('label' => esc_html__('Include Tabs', 'et_builder'), 'type' => 'checkboxes_advanced_woocommerce', 'option_category' => 'configuration', 'default' => \ET_Builder_Module_Helper_Woocommerce_Modules::get_woo_default_tabs(), 'description' => esc_html__('Here you can select the tabs that you would like to display.', 'et_builder'), 'toggle_slug' => 'main_content', 'mobile_options' => \true, 'hover' => 'tabs', 'computed_depends_on' => array('product')),
            '__tabs' => array('type' => 'computed', 'computed_callback' => array('RVX_Builder_Module_Woocommerce_Tabs', 'get_tabs'), 'computed_depends_on' => array('product'), 'computed_minimum' => array('product')),
            //Subsections
            //Graph
            'rvx_rvx_review_graph_rating_color_rvx' => array('label' => esc_html__('Average Rating Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_graph_rating_out_of_color_rvx' => array('label' => esc_html__('Max Rating Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_graph_badge_background_color_rvx' => array('label' => esc_html__('Badge Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_graph_badge_text_color_rvx' => array('label' => esc_html__('Badge Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_graph_summary_star_color_rvx' => array('label' => esc_html__('Average Rating Star Active Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_graph_summary_criteria_star_color_rvx' => array('label' => esc_html__('Average Rating Star Inactive Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_graph_aggregation_multicriteria_bar_rvx' => array('label' => esc_html__('Total Review Count Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_graph_progressbar_active_color_rvx' => array('label' => esc_html__('Summary Star Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_graph_total_reviews_color_rvx' => array('label' => esc_html__('Summary Progress Bar Active Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_summary_progress_bar_inactive_color' => array('label' => esc_html__('Summary Progress Bar Inactive Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_multi_criteria_title_color' => array('label' => esc_html__('Multi Criteria Title Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_multi_criteria_progressbar_active_color' => array('label' => esc_html__('Multi Criteria Progress Bar Active Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_multi_criteria_progressbar_inactive_color' => array('label' => esc_html__('Multi Criteria Progress Bar Inactive Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_multi_criteria_star_color' => array('label' => esc_html__('Multi Criteria Star Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_graph', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            //Review Card
            'rvx_review_item_avatar_height' => array('label' => esc_html__('Avatar Size Height', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'range', 'tab_slug' => 'advanced', 'option_category' => 'layout', 'default' => '50px', 'unitless' => \false, 'units' => array('px', '%', 'em', 'rem'), 'range_settings' => array('min' => '50', 'max' => '1000', 'step' => '10'), 'toggle_slug' => 'rvx_review_item'),
            'rvx_review_item_avatar_width' => array('label' => esc_html__('Avatar Size width', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'range', 'tab_slug' => 'advanced', 'option_category' => 'layout', 'default' => '50px', 'unitless' => \false, 'units' => array('px', '%', 'em', 'rem'), 'range_settings' => array('min' => '50', 'max' => '1000', 'step' => '10'), 'toggle_slug' => 'rvx_review_item'),
            'rvx_rvx_review_item_like_color_rvx' => array('label' => esc_html__('Card Font Size', 'reviewx'), 'type' => 'range', 'option_category' => 'typography', 'default' => '16', 'unitless' => \false, 'units' => array('px', 'em', 'rem', '%'), 'range_settings' => array('min' => '10', 'max' => '100', 'step' => '1'), 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item'),
            'rvx_rvx_review_card_item_bc_color' => array('label' => esc_html__('Card Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_item_review_date_color_rvx' => array('label' => esc_html__('Avatar Fallback Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_item_review_item_background_rvx' => array('label' => esc_html__('Name Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_item_reviewer_name_color_rvx' => array('label' => esc_html__('Rating Star Active Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_rvx_review_item_review_title_color_rvx' => array('label' => esc_html__('Rating Star Inactive Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_main_content_title_color' => array('label' => esc_html__('Review Title Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_main_content_review_description_review_date' => array('label' => esc_html__('Review Date Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_main_content_footer_action_helpful_message_text_color' => array('label' => esc_html__('Was This Helpful Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_main_content_footer_like' => array('label' => esc_html__('Review Like Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_main_content_footer_dislike' => array('label' => esc_html__('Review Dislike', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_main_content_review_description_text_color' => array('label' => esc_html__('Review Description Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_fallback_color' => array('label' => esc_html__('Avatar Fallback Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_item', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            //Form
            'rvx_template_review_form_background_color' => array('label' => esc_html__('Form Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_review_form_avatar_height' => array('label' => esc_html__('Product Height', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'range', 'tab_slug' => 'advanced', 'option_category' => 'layout', 'default' => '50px', 'unitless' => \false, 'units' => array('px', '%', 'em', 'rem'), 'range_settings' => array('min' => '50', 'max' => '1000', 'step' => '10'), 'toggle_slug' => 'rvx_review_form'),
            'rvx_review_form_avatar_width' => array('label' => esc_html__('Product Width', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'range', 'tab_slug' => 'advanced', 'option_category' => 'layout', 'default' => '50px', 'unitless' => \false, 'units' => array('px', '%', 'em', 'rem'), 'range_settings' => array('min' => '10', 'max' => '1000', 'step' => '10'), 'toggle_slug' => 'rvx_review_form'),
            'rvx_review_form_font_size' => array('label' => esc_html__('Font Size', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'range', 'tab_slug' => 'advanced', 'option_category' => 'layout', 'default' => '16px', 'unitless' => \false, 'units' => array('px', '%', 'em', 'rem'), 'range_settings' => array('min' => '60', 'max' => '1000', 'step' => '10'), 'toggle_slug' => 'rvx_review_form'),
            'rvx_template_review_form_title_color' => array('label' => esc_html__('Form Title Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form_border_color' => array('label' => esc_html__('Form Border Line Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form_product_title_color' => array('label' => esc_html__('Product Title Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form_rating_star_active_color' => array('label' => esc_html__('Rating Active Star Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form_rating_star_inactive_color' => array('label' => esc_html__('Rating Inactive Star Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form_multi_criteria_star_active_color' => array('label' => esc_html__('Multi Criteria Active Star Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form_multi_criteria_star_inactive_color' => array('label' => esc_html__('Multi Criteria Inactive Star Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form_label_color' => array('label' => esc_html__('Review Form Label Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form_review_title_input_placeholder_color' => array('label' => esc_html__('Review Form Input Placeholder Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form_input_background_color' => array('label' => esc_html__('Review Form Input Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form__attachments_icon_color' => array('label' => esc_html__('Review Attachment Icon Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form__attachments_text_color' => array('label' => esc_html__('Review Attachment Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form_submit_button_background_color' => array('label' => esc_html__('Submit Button Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_review_form_submit_button_text_color' => array('label' => esc_html__('Submit Button Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            //Filter Options
            'rvx_filter_text_color_rvx' => array('label' => esc_html__('Write Review Action Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_filter_text_button_background_color' => array('label' => esc_html__('Write Review Action Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_filter_button_font_size' => array('label' => esc_html__('Filter Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_filter_by_text_color' => array('label' => esc_html__('Filter Button Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_filter_by_font_size' => array('label' => esc_html__('Filter Dropdown Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_filter_dropdown_background_color' => array('label' => esc_html__('Dropdown Filter By Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_filter_reset_filters_text_color' => array('label' => esc_html__('Dropdown Filter Options Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_filter_button_background' => array('label' => esc_html__('Dropdown Filter Option Icon Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_filter_button_border_radius' => array('label' => esc_html__('Filter Reset Button Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_ffilter_dropdown_text_color' => array('label' => esc_html__('Filter Reset Button Background', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_sort_by_text_color' => array('label' => esc_html__('Sort By Button Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_sort_by_button_background_color' => array('label' => esc_html__('Sort By Button Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_sort_by_dropdown_text_color' => array('label' => esc_html__('Sort By Dropdown Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_sort_by_dropdown_bg_color' => array('label' => esc_html__('Sort By Dropdown Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_card_load_more' => array('label' => esc_html__('Load More Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_card_load_more_text_color' => array('label' => esc_html__('Load More Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_card_load_more_background_hover_color' => array('label' => esc_html__('Load More Hover Background Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
            'rvx_template_card_load_more_hover_text_color' => array('label' => esc_html__('Load More Text Hover Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_filter', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true),
        ]);
    }
    public function get_settings_modal_toggles()
    {
        return $this->getDesignMenu();
    }
    /**
     * Get tabs nav output
     *
     * @since 3.29
     *
     * @return string
     */
    public function get_tabs_nav()
    {
        $nav = '';
        $index = 0;
        // get_tabs_content() method is called earlier so get_tabs_nav() can reuse tabs data.
        if (!empty($this->rendered_tabs_data)) {
            foreach ($this->rendered_tabs_data as $name => $tab) {
                $index++;
                $nav .= \sprintf('<li class="%3$s%1$s"><a href="#">%2$s</a></li>', 1 === $index ? ' et_pb_tab_active' : '', esc_html($tab['title']), \sprintf('%1$s_tab', esc_attr($name)));
            }
        }
        return $nav;
    }
    /**
     * Get tabs content output
     *
     * @since 4.4.1 Fix [embed][/embed] shortcodes not working in tab content
     * @since 3.29
     *
     * @return string
     */
    public function get_tabs_content()
    {
        // Get tabs data.
        $this->rendered_tabs_data = self::get_tabs(array('product' => $this->props['product']));
        // Add tabs module classname.
        $this->add_classname('et_pb_tabs');
        // include_once 'rvxreviews_review_summary.php';
        include_once 'rvxreviews_template_one_styles.php';
        // include_once 'rvxreviews_template_two_styles.php';
        // Render tabs content output.
        $index = 0;
        $content = '';
        foreach ($this->rendered_tabs_data as $name => $tab) {
            $index++;
            $content .= \sprintf('<div class="et_pb_tab clearfix%2$s">
					<div class="et_pb_tab_content">
						%1$s
					</div><!-- .et_pb_tab_content" -->
				</div>', $tab['content'], 1 === $index ? ' et_pb_active_content' : '');
        }
        $product_id = apply_filters('rx_product_id_for_divi', \true);
        update_post_meta($product_id, '_rx_option_divi_settings', $this->props);
        return $content;
    }
    /**
     * Load comments template.
     *
     * @param string $template template to load.
     * @return string
     */
    public static function comments_template_loader($template)
    {
        if (!et_builder_tb_enabled()) {
            return $template;
        }
        $check_dirs = array(trailingslashit(get_stylesheet_directory()) . WC()->template_path(), trailingslashit(get_template_directory()) . WC()->template_path(), trailingslashit(get_stylesheet_directory()), trailingslashit(get_template_directory()), trailingslashit(WC()->plugin_path()) . 'templates/');
        if (\WC_TEMPLATE_DEBUG_MODE) {
            $check_dirs = array(\array_pop($check_dirs));
        }
        foreach ($check_dirs as $dir) {
            if (\file_exists(trailingslashit($dir) . 'single-product-reviews.php')) {
                return trailingslashit($dir) . 'single-product-reviews.php';
            }
        }
    }
    /**
     * Get tabs data
     *
     * @since 4.0.9 Avoid fetching Tabs content using `the_content` when editing TB layout.
     *
     * @param array $args Additional args.
     *
     * @return array
     */
    public static function get_tabs($args = array())
    {
        global $product, $post, $wp_query;
        /*
         * Visual builder fetches all tabs data and filter the included tab on the app to save
         * app to server request for faster user experience. Frontend passes `includes_tab` to
         * this method so it only process required tabs
         */
        $defaults = array('product' => 'current');
        $args['product'] = 'current';
        $args = wp_parse_args($args, $defaults);
        // Get actual product id based on given `product` attribute.
        $product_id = \ET_Builder_Module_Helper_Woocommerce_Modules::get_product_id($args['product']);
        add_filter('rx_product_id_for_divi', function ($args) {
            global $product;
            if ($product) {
                return $product->get_id();
            }
        });
        // Determine whether current tabs data needs global variable overwrite or not.
        $overwrite_global = et_builder_wc_need_overwrite_global($args['product']);
        // Check if TB is used
        $is_tb = et_builder_tb_enabled();
        if ($is_tb) {
            et_theme_builder_wc_set_global_objects();
        } elseif ($overwrite_global) {
            // Save current global variable for later reset.
            $original_product = $product;
            $original_post = $post;
            $original_wp_query = $wp_query;
            // Overwrite global variable.
            $post = get_post($product_id);
            $product = wc_get_product($product_id);
            $wp_query = new \WP_Query(array('p' => $product_id));
        }
        // array(
        // 	/* translators: %s: reviews count */
        // 	'title'    => sprintf( __( 'Reviews (%d)', 'woocommerce' ), $product->get_review_count() ),
        // 	'priority' => 30,
        // 	'callback' => 'comments_template',
        // )
        // Get product tabs.
        $all_tabs = apply_filters('woocommerce_product_tabs', array());
        $active_tabs = isset($args['include_tabs']) ? \explode('|', $args['include_tabs']) : \false;
        $tabs = array();
        // Get product tabs data.
        foreach ($all_tabs as $name => $tab) {
            // Skip if current tab is not included, based on `include_tabs` attribute value.
            if ($active_tabs && !\in_array($name, $active_tabs, \true)) {
                continue;
            }
            if ('description' === $name || 'additional_information' === $name) {
                continue;
            }
            if ('description' === $name) {
                if (!et_builder_tb_enabled() && !et_pb_is_pagebuilder_used($product_id)) {
                    // If selected product doesn't use builder, retrieve post content.
                    if (et_theme_builder_overrides_layout(\ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE)) {
                        $tab_content = apply_filters('et_builder_wc_description', $post->post_content);
                    } else {
                        $tab_content = $post->post_content;
                    }
                } else {
                    /*
                     * Description can't use built in callback data because it gets `the_content`
                     * which might cause infinite loop; get Divi's long description from
                     * post meta instead.
                     */
                    if (et_builder_tb_enabled()) {
                        $placeholders = et_theme_builder_wc_placeholders();
                        $tab_content = $placeholders['description'];
                    } else {
                        $tab_content = get_post_meta($product_id, \ET_BUILDER_WC_PRODUCT_LONG_DESC_META_KEY, \true);
                        // Cannot use `the_content` filter since it adds content wrapper.
                        // Content wrapper added at
                        // `includes/builder/core.php`::et_builder_add_builder_content_wrapper()
                        // This filter is documented at
                        // includes/builder/feature/woocommerce-modules.php
                        $tab_content = apply_filters('et_builder_wc_description', $tab_content);
                    }
                }
            } else {
                // Get tab value based on defined product tab's callback attribute.
                \ob_start();
                // @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found
                if ($tab['callback']) {
                    \call_user_func($tab['callback'], $name, $tab);
                }
                $tab_content = \ob_get_clean();
            }
            // Populate product tab data.
            $tabs[$name] = array('name' => $name, 'title' => $tab['title'], 'content' => $tab_content);
        }
        // Reset overwritten global variable.
        if ($is_tb) {
            et_theme_builder_wc_reset_global_objects();
        } elseif ($overwrite_global) {
            $product = $original_product;
            $post = $original_post;
            $wp_query = $original_wp_query;
        }
        return $tabs;
    }
    /**
     * Gets Multi view attributes to the Outer wrapper.
     *
     * Since we do not have control over the WooCommerce Breadcrumb markup, we inject Multi view
     * attributes on to the Outer wrapper.
     *
     * @used-by ET_Builder_Module_Tabs::render()
     *
     * @return string
     */
    public function get_multi_view_attrs()
    {
        $multi_view = et_pb_multi_view_options($this);
        $multi_view_attrs = $multi_view->render_attrs(array('attrs' => array('data-include_tabs' => '{{include_tabs}}'), 'target' => '%%order_class%%'));
        return $multi_view_attrs;
    }
    /**
     * Get current post's post type.
     *
     * @return string
     */
    public function get_post_type()
    {
        global $post, $et_builder_post_type;
        // phpcs:disable WordPress.Security.NonceVerification -- This function does not change any state, and is therefore not susceptible to CSRF.
        if (isset($_POST['et_post_type']) && !$et_builder_post_type) {
            $et_builder_post_type = sanitize_text_field($_POST['et_post_type']);
        }
        // phpcs:enable
        if (\is_a($post, 'WP_POST') && (is_admin() || !isset($et_builder_post_type))) {
            return $post->post_type;
        } else {
            $layout_type = ET_Builder_Element::get_theme_builder_layout_type();
            if ($layout_type) {
                return $layout_type;
            }
            return isset($et_builder_post_type) ? $et_builder_post_type : 'post';
        }
    }
    /**
     * Get the current theme builder layout.
     * Returns 'default' if no layout has been started.
     *
     * @since 4.0
     *
     * @return string
     */
    public static function get_theme_builder_layout_type()
    {
        $count = \count(ET_Builder_Element::$theme_builder_layout);
        if ($count > 0) {
            return ET_Builder_Element::$theme_builder_layout[$count - 1]['type'];
        }
        return 'default';
    }
}
\class_alias('Rvx\\RVX_Builder_Module_Woocommerce_Reviews', 'RVX_Builder_Module_Woocommerce_Reviews', \false);
new RVX_Builder_Module_Woocommerce_Reviews();

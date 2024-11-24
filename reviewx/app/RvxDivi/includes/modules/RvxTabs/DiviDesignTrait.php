<?php

namespace Rvx\RvxDivi\includes\modules\RvxTabs;

trait DiviDesignTrait
{
    public function getDesignMenu() : array
    {
        return ['advanced' => ['toggles' => ['rvx_review_graph' => ['tabbed_subtoggles' => \true, 'title' => esc_html__('Review Summary', 'reviewx')], 'rvx_review_item' => ['tabbed_subtoggles' => \true, 'title' => esc_html__('Review Card', 'reviewx')], 'rvx_review_form' => ['tabbed_subtoggles' => \true, 'title' => esc_html__('Review Form', 'reviewx')], 'rvx_review_fonts' => ['tabbed_subtoggles' => \true, 'title' => esc_html__('Fonts', 'reviewx')], 'rvx_review_filter' => ['tabbed_subtoggles' => \true, 'title' => esc_html__('Filter Options', 'reviewx')]]]];
    }
    public function templateOneSubsection() : array
    {
        return array('product' => \Rvx\ET_Builder_Module_Helper_Woocommerce_Modules::get_field('product', array('default' => \Rvx\ET_Builder_Module_Helper_Woocommerce_Modules::get_product_default(), 'computed_affects' => array('__tabs', 'include_tabs'))), 'include_tabs' => array('label' => esc_html__('Include Tabs', 'et_builder'), 'type' => 'checkboxes_advanced_woocommerce', 'option_category' => 'configuration', 'default' => \Rvx\ET_Builder_Module_Helper_Woocommerce_Modules::get_woo_default_tabs(), 'description' => esc_html__('Here you can select the tabs that you would like to display.', 'et_builder'), 'toggle_slug' => 'main_content', 'mobile_options' => \true, 'hover' => 'tabs', 'computed_depends_on' => array('product')), '__tabs' => array('type' => 'computed', 'computed_callback' => array('RVX_Builder_Module_Woocommerce_Tabs', 'get_tabs'), 'computed_depends_on' => array('product'), 'computed_minimum' => array('product')), 'rvx_review_section_title_colorasdasdasd' => array('label' => esc_html__('Section Title Text Color', 'reviewx'), 'description' => esc_html__('', 'reviewx'), 'type' => 'color-alpha', 'custom_color' => \true, 'tab_slug' => 'advanced', 'toggle_slug' => 'rvx_review_form', 'hover' => 'tabs', 'mobile_options' => \true, 'sticky' => \true), 'rvx_review_section_title' => array('label' => esc_html__('', 'reviewx'), 'type' => 'text', 'option_category' => 'basic_option', 'description' => esc_html__('', 'reviewx'), 'toggle_slug' => 'rvx_review_section'));
    }
    public function getAdvance() : array
    {
        return [];
    }
}

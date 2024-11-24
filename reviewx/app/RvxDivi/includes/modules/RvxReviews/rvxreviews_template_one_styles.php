<?php

namespace Rvx;

$styles = [
    //Graph
    'rvx_rvx_review_graph_rating_color_rvx' => ['selector' => '#rvx-storefront-widget .rvx-average-rating', 'custom_css' => 'color:%s!important;'],
    'rvx_rvx_review_graph_rating_out_of_color_rvx' => ['selector' => '#rvx-storefront-widget .rvx-max-rating', 'custom_css' => 'color:%s!important;'],
    'rvx_rvx_review_graph_badge_background_color_rvx' => ['selector' => '#rvx-storefront-widget .rvx-rating-badge', 'custom_css' => 'background-color:%s!important;'],
    'rvx_rvx_review_graph_badge_text_color_rvx' => ['selector' => '#rvx-storefront-widget .rvx-rating-badge__text', 'custom_css' => 'color:%s!important;'],
    'rvx_rvx_review_graph_summary_star_color_rvx' => ['selector' => '#rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-review-form__star-active', 'custom_css' => 'fill:%s!important;'],
    'rvx_rvx_review_graph_summary_criteria_star_color_rvx' => ['selector' => 'rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-review-form__star-inactive', 'custom_css' => 'color:%s!important;'],
    'rvx_rvx_review_graph_aggregation_multicriteria_bar_rvx' => ['selector' => '#rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-total-review', 'custom_css' => 'color:%s!important;'],
    'rvx_rvx_review_graph_progressbar_active_color_rvx' => ['selector' => '#rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-aggregation__row .rvx-aggregation__rating-icon path', 'custom_css' => 'fill:%s!important;'],
    'rvx_rvx_review_graph_total_reviews_color_rvx' => ['selector' => '#rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-aggregation__row .rvx-aggregation__progressbar .rvx-aggregation__progressbar-active', 'custom_css' => 'background-color:%s!important;'],
    'rvx_template_summary_progress_bar_inactive_color' => ['selector' => '#rvx-storefront-widget #rvx-storefront-widget--aggregation__summary .rvx-aggregation__row .rvx-aggregation__progressbar .rvx-aggregation__progressbar-inactive', 'custom_css' => 'background-color:%s!important;'],
    'rvx_template_multi_criteria_title_color' => ['selector' => '#rvx-storefront-widget .rvx-aggregation-multicriteria .rvx-aggregation-multicriteria__name span', 'custom_css' => 'color:%s!important;'],
    'rvx_template_multi_criteria_progressbar_active_color' => ['selector' => '.rvx-aggregation-multicriteria .rvx-aggregation__progressbar .rvx-aggregation__progressbar-active', 'custom_css' => 'background-color:%s!important;'],
    'rvx_template_multi_criteria_progressbar_inactive_color' => ['selector' => '#rvx-storefront-widget .rvx-aggregation-multicriteria .rvx-aggregation__progressbar .rvx-aggregation__progressbar-inactive', 'custom_css' => 'background-color:%s!important;'],
    'rvx_template_multi_criteria_star_color' => ['selector' => '#rvx-storefront-widget .rvx-aggregation-multicriteria .rvx-aggregation-multicriteria__total .rvx-aggregation__rating-icon path', 'custom_css' => 'fill:%s!important;'],
    //Item
    //helpfull text missing
    'rvx_review_item_avatar_width' => ['selector' => '#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-card__body .rvx-review-user__avatar, #rvx-review-details .rvx-review-user__avatar', 'custom_css' => 'width:%s!important;'],
    'rvx_review_item_avatar_height' => ['selector' => '#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-card__body .rvx-review-user__avatar, #rvx-review-details .rvx-review-user__avatar', 'custom_css' => 'height:%s!important;'],
    'rvx_rvx_review_item_like_color_rvx' => ['selector' => '.rvx-review-card .rvx-review-card__body .rvx-review-user__avatar .rvx-review-user__avatar-fallback span,rvx-review-user__avatar .rvx-review-user__avatar-fallback span,
        .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__title,#rvx-review-details .rvx-review-info .rvx-review-info__title,
        .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__date,
        #rvx-storefront-widget .rvx-review-card .rvx-review-footer .rvx-review-footer__text,#rvx-storefront-widget #rvx-review-details .rvx-review-footer .rvx-review-footer__text,
        .rvx-review-card .rvx-review-card__body .rvx-review-user .rvx-review-user__name, #rvx-review-details .rvx-review-user .rvx-review-user__name,
        .rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__feedback,#rvx-review-details .rvx-review-info .rvx-review-info__feedback, #rvx-review-details .rvx-review-info__date, #rvx-review-details .rvx-review-form__multicriteria--name', 'custom_css' => 'font-size:%s!important;'],
    'rvx_rvx_review_card_item_bc_color' => ['selector' => '#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card', 'custom_css' => 'background-color:%s!important;'],
    'rvx_rvx_review_item_review_date_color_rvx' => ['selector' => '.rvx-review-card .rvx-review-card__body .rvx-review-user__avatar .rvx-review-user__avatar-fallback span,rvx-review-user__avatar .rvx-review-user__avatar-fallback span', 'custom_css' => 'color:%s!important;'],
    'rvx_rvx_review_item_review_item_background_rvx' => ['selector' => '.rvx-review-card .rvx-review-card__body .rvx-review-user .rvx-review-user__name, #rvx-review-details .rvx-review-user .rvx-review-user__name', 'custom_css' => 'color:%s!important;'],
    'rvx_rvx_review_item_reviewer_name_color_rvx' => ['selector' => '.rvx-review-card .rvx-review-card__body .rvx-reviewer__star-active,.woocommerce-Tabs-panel #rvx-review-details .rvx-reviewer__star-active, #rvx-review-details .rvx-reviewer__star-active', 'custom_css' => 'fill:%s!important;'],
    'rvx_rvx_review_item_review_title_color_rvx' => ['selector' => '.rvx-review-card .rvx-review-card__body .rvx-reviewer__star-inactive,#rvx-review-details__body .rvx-reviewer__star-inactive', 'custom_css' => 'fill:%s!important;'],
    'rvx_template_main_content_title_color' => ['selector' => '.rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__title,#rvx-review-details .rvx-review-info .rvx-review-info__title', 'custom_css' => 'color:%s!important;'],
    'rvx_template_main_content_review_description_review_date' => ['selector' => '.rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__date, #rvx-review-details .rvx-review-info__date', 'custom_css' => 'color:%s!important;'],
    'rvx_template_main_content_footer_action_helpful_message_text_color' => ['selector' => '#rvx-storefront-widget .rvx-review-card .rvx-review-footer .rvx-review-footer__text,#rvx-storefront-widget #rvx-review-details .rvx-review-footer .rvx-review-footer__text', 'custom_css' => 'color:%s!important;'],
    'rvx_template_main_content_footer_like' => ['selector' => '#rvx-storefront-widget .rvx-review-card .rvx-review-footer__thumbs--like-icon path,#rvx-storefront-widget #rvx-review-details .rvx-review-footer__thumbs--like-icon path', 'custom_css' => 'fill:%s!important;'],
    'rvx_template_main_content_footer_dislike' => ['selector' => '#rvx-storefront-widget .rvx-review-card .rvx-review-footer__thumbs--dislike-icon path,#rvx-storefront-widget #rvx-review-details .rvx-review-footer__thumbs--dislike-icon path', 'custom_css' => 'fill:%s!important;'],
    'rvx_template_main_content_review_description_text_color' => ['selector' => '.rvx-review-card .rvx-review-card__body .rvx-review-info .rvx-review-info__feedback,#rvx-review-details .rvx-review-info .rvx-review-info__feedback', 'custom_css' => 'color:%s!important;'],
    'rvx_template_fallback_color' => ['selector' => '#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-card__body .rvx-review-user__avatar, #rvx-review-details .rvx-review-user__avatar', 'custom_css' => 'background-color:%s!important;'],
    //    //Form
    //    //rating icon fill color missing
    'rvx_template_review_form_background_color' => ['selector' => '#rvx-review-form__wrapper', 'custom_css' => 'background:%s !important;'],
    'rvx_review_form_avatar_height' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__product .rvx-review-form__product--image', 'custom_css' => 'height:%s !important;'],
    'rvx_review_form_avatar_width' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__product .rvx-review-form__product--image', 'custom_css' => 'width:%s !important;'],
    'rvx_template_review_form_title_color' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form .rvx-review-form__title', 'custom_css' => 'color:%s !important;'],
    'rvx_template_review_form_border_color' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form .rvx-review-form__line', 'custom_css' => 'background:%s;'],
    'rvx_template_review_form_product_title_color' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__product .rvx-review-form__product--title', 'custom_css' => 'color:%s!important;'],
    'rvx_template_review_form_rating_star_active_color' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__rating .rvx-review-form__star-active', 'custom_css' => 'fill:%s !important;'],
    'rvx_template_review_form_rating_star_inactive_color' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__rating .rvx-review-form__star-inactive', 'custom_css' => 'fill:%s!important;'],
    'rvx_template_review_form_multi_criteria_star_active_color' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__multicriteria .rvx-review-form__star-active', 'custom_css' => 'fill:%s!important;'],
    'rvx_template_review_form_multi_criteria_star_inactive_color' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__multicriteria .rvx-review-form__star-inactive', 'custom_css' => 'fill:%s!important;'],
    'rvx_template_review_form_label_color' => ['selector' => ' #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__title .rvx-review-form__title--name,
             #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__description .rvx-review-form__description--title,
             #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__rating .rvx-review-form__rating--name,
             #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__multicriteria .rvx-review-form__multicriteria--name,
             #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__user .rvx-review-form__user--name,
             #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__email .rvx-review-form__email--name,
             #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__attachment .rvx-review-form__attachment--name,
             #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__attachment--inner .rvx-review-form__mark-anonymous,
             #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__recommended .rvx-review-form__recommended--name,
             #rvx-review-form__wrapper .rvx-review-form .rvx-review-form__inner .rvx-review-form__recommended label', 'custom_css' => 'color:%s!important;'],
    'rvx_template_review_form_review_title_input_placeholder_color' => ['selector' => ' #rvx-review-form__wrapper input::placeholder,
         #rvx-review-form__wrapper textarea::placeholder', 'custom_css' => 'color:%s!important;'],
    'rvx_template_review_form_input_background_color' => ['selector' => ' #rvx-review-form__wrapper input,
         #rvx-review-form__wrapper textarea', 'custom_css' => 'background:%s!important;;'],
    'rvx_template_review_form__attachments_icon_color' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form__attachment .rvx-review-form__attachment--inner .rvx-review-form__attachment--upload--icon', 'custom_css' => 'color:%s!important;'],
    'rvx_template_review_form__attachments_text_color' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form__attachment .rvx-review-form__attachment--inner .rvx-review-form__attachment--upload--title', 'custom_css' => 'color:%s!important;'],
    'rvx_template_review_form_submit_button_background_color' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"],
                        #rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"]:focus', 'custom_css' => 'background-color:%s!important;'],
    'rvx_template_review_form_submit_button_text_color' => ['selector' => '#rvx-review-form__wrapper .rvx-review-form__footer .rvx-review-form__submit--button[type="submit"]', 'custom_css' => 'color:%s!important;'],
    // Filter
    'rvx_filter_text_color_rvx' => ['selector' => '#rvx-storefront-widget #rvx-review-filter .rvx-review-write__button', 'custom_css' => 'background:%s!important;'],
    'rvx_filter_text_button_background_color' => ['selector' => '#rvx-storefront-widget #rvx-review-filter .rvx-review-write__button', 'custom_css' => 'color:%s!important;'],
    'rvx_filter_button_font_size' => ['selector' => '#rvx-storefront-widget #rvx-review-filter .rvx-review-filter__button', 'custom_css' => 'background:%s!important;'],
    'rvx_filter_by_text_color' => ['selector' => '#rvx-storefront-widget #rvx-review-filter .rvx-review-filter__button', 'custom_css' => 'color:%s!important;'],
    'rvx_filter_by_font_size' => ['selector' => '#rvx-review-filter .rvx-review-filter-wrapper,
            #rvx-storefront-widget #rvx-review-filter .rvx-review-filter-wrapper .rvx-review-filter__wrapper-inner,
            #rvx-storefront-widget #rvx-review-filter .rvx-review-filter-wrapper .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer', 'custom_css' => 'background:%s!important;'],
    'rvx_filter_dropdown_background_color' => ['selector' => '#rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__title', 'custom_css' => 'color:%s;'],
    'rvx_filter_reset_filters_text_color' => ['selector' => ' #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__rating .rvx-review-filter-wrapper__rating--text,
              #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__rating .rvx-review-filter-wrapper__rating-wrapper .rvx-review-filter-wrapper__rating-inner .rvx-review-filter__wrapper__rating--radio-group__option-label,
             #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__attachment .rvx-review-filter-wrapper__attachment--text,
             #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__attachment .rvx-review-filter-wrapper__attachment-wrapper .rvx-review-filter-wrapper__attachment-inner .rvx-review-filter__wrapper__attachment--radio-group__option-label', 'custom_css' => 'color:%s;'],
    'rvx_filter_button_background' => ['selector' => ' #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__rating .rvx-review-filter-wrapper__rating-inner--icon,
        #rvx-storefront-widget #rvx-review-filter .rvx-review-filter__wrapper-inner .rvx-review-filter-wrapper__outer .rvx-review-filter-wrapper__attachment .rvx-review-filter-wrapper__attachment-inner--icon', 'custom_css' => 'color:%s!important;'],
    'rvx_filter_button_border_radius' => ['selector' => '#rvx-storefront-widget #rvx-review-filter .rvx-review-filter-wrapper__footer button', 'custom_css' => 'color:%s!important;'],
    'rvx_ffilter_dropdown_text_color' => ['selector' => '#rvx-storefront-widget #rvx-review-filter .rvx-review-filter-wrapper__footer button', 'custom_css' => 'background-color:%s!important;'],
    'rvx_sort_by_text_color' => ['selector' => '#rvx-storefront-widget #rvx-review-filter .rvx-review-sort__button', 'custom_css' => 'color:%s!important;'],
    'rvx_sort_by_button_background_color' => ['selector' => '#rvx-storefront-widget #rvx-review-filter .rvx-review-sort__button', 'custom_css' => 'background-color:%s!important;'],
    'rvx_template_sort_by_dropdown_text_color' => ['selector' => '#rvx-storefront-widget .rvx-review-sort-wrapper__outer', 'custom_css' => 'color:%s!important;'],
    'rvx_template_sort_by_dropdown_bg_color' => ['selector' => '#rvx-storefront-widget #rvx-review-filter .rvx-review-sort-wrapper, #rvx-storefront-widget .rvx-review-sort-wrapper__outer', 'custom_css' => 'background:%s!important;'],
    'rvx_template_card_load_more' => ['selector' => '#rvx-storefront-widget button', 'custom_css' => 'background:%s!important;'],
    'rvx_template_card_load_more_text_color' => ['selector' => '#rvx-storefront-widget button', 'custom_css' => 'color:%s!important;'],
    'rvx_template_card_load_more_background_hover_color' => ['selector' => '#rvx-storefront-widget button:hover', 'custom_css' => 'background:%s!important;'],
    'rvx_template_card_load_more_hover_text_color' => ['selector' => '#rvx-storefront-widget button:hover', 'custom_css' => 'background:%s!important;'],
];
foreach ($styles as $key => $item) {
    // Retrieve the property value
    $value = $this->props[$key] ?? '';
    if ($value != '') {
        // Construct the CSS declaration dynamically using the template
        $css = \sprintf($item['custom_css'], esc_attr($value));
        ET_Builder_Element::set_style('et_pb_wc_tabs_for_ReviewX', array('selector' => $item['selector'], 'declaration' => $css));
    }
}

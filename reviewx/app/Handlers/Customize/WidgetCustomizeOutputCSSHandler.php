<?php

namespace ReviewX\Handlers\Customize;

class WidgetCustomizeOutputCSSHandler
{
    public function __invoke() : void
    {
        if (!did_action('elementor/loaded')) {
            $this->reviewx_load_customizer_output_css();
        }
    }
    public function reviewx_load_customizer_output_css() : void
    {
        /*
         * ReviewX - General Settings
         */
        // Active Rating Stars: [Background Color]
        //$reviewx_general_reviews_active_rating_stars_background_color = get_theme_mod( 'rvx_general_reviews_active_rating_stars_background_color', '#FCCE08');
        /*
         * ReviewX - Reviews Overview
         */
        // Rating out of
        $reviewx_reviews_overview_rating_out_of_text_color = get_theme_mod('rvx_reviews_overview_rating_out_of_text_color', '#424242');
        $reviewx_reviews_overview_rating_out_of_text_font_size = get_theme_mod('rvx_reviews_overview_rating_out_of_text_font_size', 43.942);
        // Rating out of Total
        $reviewx_reviews_overview_rating_out_of_total_text_color = get_theme_mod('rvx_reviews_overview_rating_out_of_total_text_color', '#BDBDBD');
        $reviewx_reviews_overview_rating_out_of_total_text_font_size = get_theme_mod('rvx_reviews_overview_rating_out_of_total_text_font_size', 24);
        // Rating Badge
        $reviewx_reviews_overview_rating_badge_background_color = get_theme_mod('rvx_reviews_overview_rating_badge_background_color', '#22C55E');
        $reviewx_reviews_overview_rating_badge_text_color = get_theme_mod('rvx_reviews_overview_rating_badge_text_color', '#FFFFFF');
        // Total Reviews Count
        $reviewx_reviews_overview_total_reviews_text_color = get_theme_mod('rvx_reviews_overview_total_reviews_text_color', '#424242');
        $reviewx_reviews_overview_total_reviews_text_font_size = get_theme_mod('rvx_reviews_overview_total_reviews_text_font_size', 16);
        // Rating Overview Chart
        $reviewx_reviews_overview_rating_overview_chart_text_color = get_theme_mod('rvx_reviews_overview_rating_overview_chart_text_color', '#424242');
        $reviewx_reviews_overview_rating_overview_chart_text_font_size = get_theme_mod('rvx_reviews_overview_rating_overview_chart_text_font_size', 14);
        // Product Recommendation Text
        $reviewx_reviews_overview_product_recommendation_background_color = get_theme_mod('rvx_reviews_overview_product_recommendation_background_color', '#F5F5F5');
        $reviewx_reviews_overview_product_recommendation_border_color = get_theme_mod('rvx_reviews_overview_product_recommendation_border_color', '#F5F5F5');
        $reviewx_reviews_overview_product_recommendation_border_radius = get_theme_mod('rvx_reviews_overview_product_recommendation_border_radius', 4);
        $reviewx_reviews_overview_product_recommendation_text_color = get_theme_mod('rvx_reviews_overview_product_recommendation_text_color', '#424242');
        $reviewx_reviews_overview_product_recommendation_text_font_size = get_theme_mod('rvx_reviews_overview_product_recommendation_text_font_size', 14);
        // Review Criteria Text
        $reviewx_reviews_overview_review_criteria_text_color = get_theme_mod('rvx_reviews_overview_review_criteria_text_color', '#424242');
        $reviewx_reviews_overview_review_criteria_text_font_size = get_theme_mod('rvx_reviews_overview_review_criteria_text_font_size', 14);
        /*
         * ReviewX - Filter Buttons
         */
        // Filter Button
        $reviewx_filter_button_text_color = get_theme_mod('rvx_filter_button_text_color', '#424242');
        $reviewx_filter_button_background_color = get_theme_mod('rvx_filter_button_background_color', '#F0F0F1');
        $reviewx_filter_button_border_color = get_theme_mod('rvx_filter_button_border_color', '#BDBDBD');
        $reviewx_filter_button_border_radius = get_theme_mod('rvx_filter_button_border_radius', 4);
        // Filter Dropdown menu
        $reviewx_filter_dropdown_menu_text_color = get_theme_mod('rvx_filter_dropdown_menu_text_color', '#616161');
        $reviewx_filter_dropdown_menu_background_color = get_theme_mod('rvx_filter_dropdown_menu_background_color', '#FFFFFF');
        $reviewx_filter_dropdown_menu_border_color = get_theme_mod('rvx_filter_dropdown_menu_border_color', '#FFFFFF');
        $reviewx_filter_dropdown_menu_border_radius = get_theme_mod('rvx_filter_dropdown_menu_border_radius', 4);
        $reviewx_filter_dropdown_menu_text_font_size = get_theme_mod('rvx_filter_dropdown_menu_text_font_size', 14);
        // Filter Dropdown [Filter by]
        $reviewx_filter_by_text_color = get_theme_mod('rvx_filter_by_text_color', '#424242');
        $reviewx_filter_by_text_font_size = get_theme_mod('rvx_filter_by_text_font_size', 16);
        // Filter Dropdown menu: [Reset filters] button
        $reviewx_filter_reset_button_text_color = get_theme_mod('rvx_filter_reset_button_text_color', '#0043DD');
        $reviewx_filter_reset_button_background_color = get_theme_mod('rvx_filter_reset_button_background_color', '#FFFFFF');
        $reviewx_filter_reset_button_border_color = get_theme_mod('rvx_filter_reset_button_border_color', '#FFFFFF');
        $reviewx_filter_reset_button_border_radius = get_theme_mod('rvx_filter_reset_button_border_radius', 4);
        // Filter Dropdown menu: [Apply] button
        $reviewx_filter_apply_button_text_color = get_theme_mod('rvx_filter_apply_button_text_color', '#FFFFFF');
        $reviewx_filter_apply_button_background_color = get_theme_mod('rvx_filter_apply_button_background_color', '#0043DD');
        $reviewx_filter_apply_button_border_color = get_theme_mod('rvx_filter_apply_button_border_color', '#0043DD');
        $reviewx_filter_apply_button_border_radius = get_theme_mod('rvx_filter_apply_button_border_radius', 4);
        // Sort Button
        $reviewx_sort_button_text_color = get_theme_mod('rvx_sort_button_text_color', '#424242');
        $reviewx_sort_button_background_color = get_theme_mod('rvx_sort_button_background_color', '#F0F0F1');
        $reviewx_sort_button_border_color = get_theme_mod('rvx_sort_button_border_color', '#BDBDBD');
        $reviewx_sort_button_border_radius = get_theme_mod('rvx_sort_button_border_radius', 4);
        // Sort Dropdown menu
        $reviewx_sort_dropdown_menu_text_color = get_theme_mod('rvx_sort_dropdown_menu_text_color', '#616161');
        $reviewx_sort_dropdown_menu_background_color = get_theme_mod('rvx_sort_dropdown_menu_background_color', '#FFFFFF');
        $reviewx_sort_dropdown_menu_border_color = get_theme_mod('rvx_sort_dropdown_menu_border_color', '#FFFFFF');
        $reviewx_sort_dropdown_menu_border_radius = get_theme_mod('rvx_sort_dropdown_menu_border_radius', 4);
        $reviewx_sort_dropdown_menu_text_font_size = get_theme_mod('rvx_sort_dropdown_menu_text_font_size', 14);
        // Write a Review Button
        //$reviewx_write_review_button_text_color = get_theme_mod('rvx_write_review_button_text_color', '#424242');
        //$reviewx_write_review_button_background_color = get_theme_mod( 'rvx_write_review_button_background_color', '#BDBDBD');
        $reviewx_write_review_button_border_color = get_theme_mod('rvx_write_review_button_border_color', '#0043DD');
        $reviewx_write_review_button_border_radius = get_theme_mod('rvx_write_review_button_border_radius', 4);
        /*
         * ReviewX - Review Items
         */
        // Review Items: Card
        $reviewx_reviews_items_card_background_color = get_theme_mod('rvx_reviews_items_card_background_color', '#F5F5F5');
        $reviewx_reviews_items_card_border_color = get_theme_mod('rvx_reviews_items_card_border_color', '#F5F5F5');
        $reviewx_reviews_items_card_border_radius = get_theme_mod('rvx_reviews_items_card_border_radius', 6);
        $reviewx_reviews_items_card_inline_padding = get_theme_mod('rvx_reviews_items_card_inline_padding', 8);
        // Review Items: Reviewer Name
        $reviewx_reviews_items_reviewer_name_text_color = get_theme_mod('rvx_reviews_items_reviewer_name_text_color', '#424242');
        $reviewx_reviews_items_reviewer_name_text_font_size = get_theme_mod('rvx_reviews_items_reviewer_name_text_font_size', 20);
        // Review Items: Review Title
        $reviewx_reviews_items_title_text_color = get_theme_mod('rvx_reviews_items_title_text_color', '#424242');
        $reviewx_reviews_items_title_text_font_size = get_theme_mod('rvx_reviews_items_title_text_font_size', 20);
        // Review Items: Review Date
        $reviewx_reviews_items_date_text_color = get_theme_mod('rvx_reviews_items_date_text_color', '#757575');
        $reviewx_reviews_items_date_text_font_size = get_theme_mod('rvx_reviews_items_date_text_font_size', 14);
        // Review Items: Description
        $reviewx_reviews_items_description_text_color = get_theme_mod('rvx_reviews_items_description_text_color', '#757575');
        $reviewx_reviews_items_description_text_font_size = get_theme_mod('rvx_reviews_items_description_text_font_size', 14);
        /*
         * ReviewX - Review Form
         */
        // Form
        $reviewx_input_form_background_color = get_theme_mod('rvx_input_form_background_color', '#F5F5F5');
        // Disabled - already available in ReviewX-> Widget Settings
        $reviewx_input_form_border_color = get_theme_mod('rvx_input_form_border_color', '#F5F5F5');
        $reviewx_input_form_border_radius = get_theme_mod('rvx_input_form_border_radius', 6);
        // Form: Title
        $reviewx_input_form_title_text_color = get_theme_mod('rvx_input_form_title_text_color', '#424242');
        $reviewx_input_form_title_text_font_size = get_theme_mod('rvx_input_form_title_text_font_size', 18);
        // Form: Product Name
        $reviewx_input_form_product_name_text_color = get_theme_mod('rvx_input_form_product_name_text_color', '#424242');
        $reviewx_input_form_product_name_text_font_size = get_theme_mod('rvx_input_form_product_name_text_font_size', 18);
        // Form: Criteria Title
        $reviewx_input_form_criteria_title_text_color = get_theme_mod('rvx_input_form_criteria_title_text_color', '#424242');
        $reviewx_input_form_criteria_title_text_font_size = get_theme_mod('rvx_input_form_criteria_title_text_font_size', 16);
        // Form: Review Title
        $reviewx_input_form_review_title_text_color = get_theme_mod('rvx_input_form_review_title_text_color', '#424242');
        $reviewx_input_form_review_title_text_font_size = get_theme_mod('rvx_input_form_review_title_text_font_size', 16);
        // Form: Description Title
        $reviewx_input_form_description_title_text_color = get_theme_mod('rvx_input_form_description_title_text_color', '#424242');
        $reviewx_input_form_description_title_text_font_size = get_theme_mod('rvx_input_form_description_title_text_font_size', 16);
        // Form: Full Name
        $reviewx_input_form_full_name_text_color = get_theme_mod('rvx_input_form_full_name_text_color', '#424242');
        $reviewx_input_form_full_name_text_font_size = get_theme_mod('rvx_input_form_full_name_text_font_size', 16);
        // Form: Email Address
        $reviewx_input_form_email_address_text_color = get_theme_mod('rvx_input_form_email_address_text_color', '#424242');
        $reviewx_input_form_email_address_text_font_size = get_theme_mod('rvx_input_form_email_address_text_font_size', 16);
        // Form: Attachment Title
        $reviewx_input_form_attachment_title_text_color = get_theme_mod('rvx_input_form_attachment_title_text_color', '#424242');
        $reviewx_input_form_attachment_title_text_font_size = get_theme_mod('rvx_input_form_attachment_title_text_font_size', 16);
        // Form: Mark as Anonymous
        $reviewx_input_form_mark_anonymous_text_color = get_theme_mod('rvx_input_form_mark_anonymous_text_color', '#424242');
        $reviewx_input_form_mark_anonymous_text_font_size = get_theme_mod('rvx_input_form_mark_anonymous_text_font_size', 16);
        // Form: Recommended?
        $reviewx_input_form_recommended_text_color = get_theme_mod('rvx_input_form_recommended_text_color', '#424242');
        $reviewx_input_form_recommended_text_font_size = get_theme_mod('rvx_input_form_recommended_text_font_size', 16);
        // Form: Submit Review Button
        //$reviewx_submit_review_button_text_color = get_theme_mod('rvx_submit_review_button_text_color', '#FFFFFF');
        //$reviewx_submit_review_button_background_color = get_theme_mod( 'rvx_submit_review_button_background_color', '#0043DD');
        $reviewx_submit_review_button_border_color = get_theme_mod('rvx_submit_review_button_border_color', '#0043DD');
        $reviewx_submit_review_button_border_radius = get_theme_mod('rvx_submit_review_button_border_radius', 6);
        ?>

        <style type="text/css">

            /*
             * ReviewX - Reviews Overview
             */
            #reviewx-storefront-widget p.reviewx-rating-out-of,
            p.reviewx-rating-out-of{
                color: <?php 
        echo \esc_attr($reviewx_reviews_overview_rating_out_of_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_reviews_overview_rating_out_of_text_font_size);
        ?>px !important;
            }

            #reviewx-storefront-widget span.reviewx-rating-total,
            span.reviewx-rating-total{
                color: <?php 
        echo \esc_attr($reviewx_reviews_overview_rating_out_of_total_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_reviews_overview_rating_out_of_total_text_font_size);
        ?>px !important;
            }

            #reviewx-storefront-widget .reviewx-rating-badge,
            .reviewx-rating-badge{
                background-color: <?php 
        echo \esc_attr($reviewx_reviews_overview_rating_badge_background_color);
        ?> !important;
                color: <?php 
        echo \esc_attr($reviewx_reviews_overview_rating_badge_text_color);
        ?> !important;
            }

            /*
            #reviewx-storefront-widget .reviewx-review-form__star-active,
            .reviewx-review-form__star-active{
                fill:<?php 
        //echo esc_attr($reviewx_general_reviews_active_rating_stars_background_color);
        ?>;
            }
            #reviewx-storefront-widget .reviewx-review-form__star-active-half-star,
            .reviewx-review-form__star-active-half-star{
                stop-color:<?php 
        //echo esc_attr($reviewx_general_reviews_active_rating_stars_background_color);
        ?>;
            }
            #reviewx-storefront-widget .reviewx-aggregation__rating-icon path,
            .reviewx-aggregation__rating-icon path{
                fill:<?php 
        //echo esc_attr($reviewx_general_reviews_active_rating_stars_background_color);
        ?>;
            }
            #reviewx-storefront-widget .reviewx-aggregation__rating-icon,
            .reviewx-aggregation__rating-icon{
                fill:<?php 
        //echo esc_attr($reviewx_general_reviews_active_rating_stars_background_color);
        ?>;
            }
            */
            
            #reviewx-storefront-widget p.reviewx-total-review,
            p.reviewx-total-review{
                color: <?php 
        echo \esc_attr($reviewx_reviews_overview_total_reviews_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_reviews_overview_total_reviews_text_font_size);
        ?>px !important;
            }

            #reviewx-storefront-widget tr.reviewx-aggregation__row td span,
            tr.reviewx-aggregation__row td span{
                color: <?php 
        echo \esc_attr($reviewx_reviews_overview_rating_overview_chart_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_reviews_overview_rating_overview_chart_text_font_size);
        ?>px !important;
            }
            #reviewx-storefront-widget .reviewx-recommendation-count,
            .reviewx-recommendation-count{
                background-color: <?php 
        echo \esc_attr($reviewx_reviews_overview_product_recommendation_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo \esc_attr($reviewx_reviews_overview_product_recommendation_border_color);
        ?> !important;
                border-radius: <?php 
        echo \esc_attr($reviewx_reviews_overview_product_recommendation_border_radius);
        ?>px !important;
            }
            #reviewx-storefront-widget .reviewx-recommendation-count p,
            .reviewx-recommendation-count p{
                color: <?php 
        echo \esc_attr($reviewx_reviews_overview_product_recommendation_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_reviews_overview_product_recommendation_text_font_size);
        ?>px !important;
            }

            #reviewx-storefront-widget .reviewx-recommendation-count svg,
            .reviewx-recommendation-count svg{
                width:<?php 
        echo \esc_attr($reviewx_reviews_overview_product_recommendation_text_font_size);
        ?>;
                height:<?php 
        echo \esc_attr($reviewx_reviews_overview_product_recommendation_text_font_size);
        ?>;
            }
            
            #reviewx-storefront-widget .reviewx-aggregation-multicriteria span,
            .reviewx-aggregation-multicriteria span{
                color: <?php 
        echo \esc_attr($reviewx_reviews_overview_review_criteria_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_reviews_overview_review_criteria_text_font_size);
        ?>px !important;
            }


            /*
             * ReviewX - Filter Buttons
             */
            #reviewx-storefront-widget .reviewx-review-filter__button,
            .reviewx-review-filter__button{
                color: <?php 
        echo \esc_attr($reviewx_filter_button_text_color);
        ?> !important;
                background-color: <?php 
        echo \esc_attr($reviewx_filter_button_background_color);
        ?> !important;
            }
            #reviewx-storefront-widget .reviewx-review-filter__button,
            .reviewx-review-filter__button{
                border:solid 1px <?php 
        echo \esc_attr($reviewx_filter_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo \esc_attr($reviewx_filter_button_border_radius);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-filter-wrapper,
            .reviewx-review-filter-wrapper{
                color: <?php 
        echo \esc_attr($reviewx_filter_dropdown_menu_text_color);
        ?> !important;
                background-color: <?php 
        echo \esc_attr($reviewx_filter_dropdown_menu_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo \esc_attr($reviewx_filter_dropdown_menu_border_color);
        ?> !important;
                border-radius: <?php 
        echo \esc_attr($reviewx_filter_dropdown_menu_border_radius);
        ?>px !important;
                font-size: <?php 
        echo \esc_attr($reviewx_filter_dropdown_menu_text_font_size);
        ?>px !important;
            }

            #reviewx-storefront-widget .reviewx-review-filter-wrapper p.reviewx-review-filter-wrapper__title,
            .reviewx-review-filter-wrapper p.reviewx-review-filter-wrapper__title{
                color: <?php 
        echo \esc_attr($reviewx_filter_by_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_filter_by_text_font_size);
        ?>px !important;
            }

            #reviewx-storefront-widget .reviewx-review-filter-wrapper__footer-reset-button,
            .reviewx-review-filter-wrapper__footer-reset-button{
                color: <?php 
        echo \esc_attr($reviewx_filter_reset_button_text_color);
        ?> !important;
                background-color: <?php 
        echo \esc_attr($reviewx_filter_reset_button_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo \esc_attr($reviewx_filter_reset_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo \esc_attr($reviewx_filter_reset_button_border_radius);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-filter-wrapper__footer-save-button,
            .reviewx-review-filter-wrapper__footer-save-button{
                color: <?php 
        echo \esc_attr($reviewx_filter_apply_button_text_color);
        ?> !important;
                background-color: <?php 
        echo \esc_attr($reviewx_filter_apply_button_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo \esc_attr($reviewx_filter_apply_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo \esc_attr($reviewx_filter_apply_button_border_radius);
        ?>px !important;
            }
            

            #reviewx-storefront-widget .reviewx-review-sort__button,
            .reviewx-review-sort__button{
                color: <?php 
        echo \esc_attr($reviewx_sort_button_text_color);
        ?> !important;
                background-color: <?php 
        echo \esc_attr($reviewx_sort_button_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo \esc_attr($reviewx_sort_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo \esc_attr($reviewx_sort_button_border_radius);
        ?>px !important;
            }

            #reviewx-storefront-widget .reviewx-review-sort-wrapper__outer,
            .reviewx-review-sort-wrapper__outer{
                color: <?php 
        echo \esc_attr($reviewx_sort_dropdown_menu_text_color);
        ?> !important;
                background-color: <?php 
        echo \esc_attr($reviewx_sort_dropdown_menu_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo \esc_attr($reviewx_sort_dropdown_menu_border_color);
        ?> !important;
                border-radius: <?php 
        echo \esc_attr($reviewx_sort_dropdown_menu_border_radius);
        ?>px !important;
                font-size: <?php 
        echo \esc_attr($reviewx_sort_dropdown_menu_text_font_size);
        ?>px !important;
            }

            #reviewx-storefront-widget .reviewx-review-write__button,
            .reviewx-review-write__button{
                border:solid 1px <?php 
        echo \esc_attr($reviewx_write_review_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo \esc_attr($reviewx_write_review_button_border_radius);
        ?>px !important;
            }

            /*
             * ReviewX - Review Items
             */
            #reviewx-storefront-widget .reviewx-review-wrapper .reviewx-review-card,
            .reviewx-review-wrapper .reviewx-review-card{
                background-color: <?php 
        echo \esc_attr($reviewx_reviews_items_card_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo \esc_attr($reviewx_reviews_items_card_border_color);
        ?> !important;
                border-radius: <?php 
        echo \esc_attr($reviewx_reviews_items_card_border_radius);
        ?>px !important;
                padding: <?php 
        echo \esc_attr($reviewx_reviews_items_card_inline_padding);
        ?>px !important;
            }

            #reviewx-storefront-widget .reviewx-review-wrapper .reviewx-review-card .reviewx-review-user__name,
            .reviewx-review-wrapper .reviewx-review-card .reviewx-review-user__name{
                color: <?php 
        echo \esc_attr($reviewx_reviews_items_reviewer_name_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_reviews_items_reviewer_name_text_font_size);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-wrapper .reviewx-review-card .reviewx-review-info__title,
            .reviewx-review-wrapper .reviewx-review-card .reviewx-review-info__title{
                color: <?php 
        echo \esc_attr($reviewx_reviews_items_title_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_reviews_items_title_text_font_size);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-wrapper .reviewx-review-card .reviewx-review-info__date,
            .reviewx-review-wrapper .reviewx-review-card .reviewx-review-info__date{
                color: <?php 
        echo \esc_attr($reviewx_reviews_items_date_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_reviews_items_date_text_font_size);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-wrapper .reviewx-review-card .reviewx-review-info__feedback,
            .reviewx-review-wrapper .reviewx-review-card .reviewx-review-info__feedback{
                color: <?php 
        echo \esc_attr($reviewx_reviews_items_description_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_reviews_items_description_text_font_size);
        ?>px !important;
            }
            
            
            /*
             * ReviewX - Review Form
             */
            #reviewx-storefront-widget #reviewx-review-form__wrapper,
            #reviewx-review-form__wrapper{
                background-color: <?php 
        echo \esc_attr($reviewx_input_form_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo \esc_attr($reviewx_input_form_border_color);
        ?> !important;
                border-radius: <?php 
        echo \esc_attr($reviewx_input_form_border_radius);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-form__title,
            .reviewx-review-form__title{
                color: <?php 
        echo \esc_attr($reviewx_input_form_title_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_input_form_title_text_font_size);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-form__product--title,
            .reviewx-review-form__product--title{
                color: <?php 
        echo \esc_attr($reviewx_input_form_product_name_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_input_form_product_name_text_font_size);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-form__multicriteria--name,
            .reviewx-review-form__multicriteria--name{
                color: <?php 
        echo \esc_attr($reviewx_input_form_criteria_title_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_input_form_criteria_title_text_font_size);
        ?>px !important;
            }

            #reviewx-storefront-widget .reviewx-review-form__title--name,
            .reviewx-review-form__title--name{
                color: <?php 
        echo \esc_attr($reviewx_input_form_review_title_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_input_form_review_title_text_font_size);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-form__description-title,
            .reviewx-review-form__description-title{
                color: <?php 
        echo \esc_attr($reviewx_input_form_description_title_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_input_form_description_title_text_font_size);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-form__user--name,
            .reviewx-review-form__user--name{
                color: <?php 
        echo \esc_attr($reviewx_input_form_full_name_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_input_form_full_name_text_font_size);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-form__email--name,
            .reviewx-review-form__email--name{
                color: <?php 
        echo \esc_attr($reviewx_input_form_email_address_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_input_form_email_address_text_font_size);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-form__attachment--name,
            .reviewx-review-form__attachment--name{
                color: <?php 
        echo \esc_attr($reviewx_input_form_attachment_title_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_input_form_attachment_title_text_font_size);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-form__mark-anonymous,
            .reviewx-review-form__mark-anonymous{
                color: <?php 
        echo \esc_attr($reviewx_input_form_mark_anonymous_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_input_form_mark_anonymous_text_font_size);
        ?>px !important;
            }
            
            #reviewx-storefront-widget .reviewx-review-form__recommended--name,
            .reviewx-review-form__recommended--name{
                color: <?php 
        echo \esc_attr($reviewx_input_form_recommended_text_color);
        ?> !important;
                font-size: <?php 
        echo \esc_attr($reviewx_input_form_recommended_text_font_size);
        ?>px !important;
            }

            #reviewx-storefront-widget .reviewx-review-form__submit--button,
            .reviewx-review-form__submit--button{
                border:solid 1px <?php 
        echo \esc_attr($reviewx_submit_review_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo \esc_attr($reviewx_submit_review_button_border_radius);
        ?>px !important;
            }
        </style>
        <?php 
    }
}

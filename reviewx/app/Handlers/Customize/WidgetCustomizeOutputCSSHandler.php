<?php

namespace Rvx\Handlers\Customize;

class WidgetCustomizeOutputCSSHandler
{
    public function __invoke() : void
    {
        if (!did_action('elementor/loaded')) {
            $this->rvx_load_customizer_output_css();
        }
    }
    public function rvx_load_customizer_output_css() : void
    {
        /*
         * ReviewX - General Settings
         */
        // Active Rating Stars: [Background Color]
        //$rvx_general_reviews_active_rating_stars_background_color = get_theme_mod( 'rvx_general_reviews_active_rating_stars_background_color', '#FCCE08');
        /*
         * ReviewX - Reviews Overview
         */
        // Rating out of
        $rvx_reviews_overview_rating_out_of_text_color = get_theme_mod('rvx_reviews_overview_rating_out_of_text_color', '#424242');
        $rvx_reviews_overview_rating_out_of_text_font_size = get_theme_mod('rvx_reviews_overview_rating_out_of_text_font_size', 43.942);
        // Rating out of Total
        $rvx_reviews_overview_rating_out_of_total_text_color = get_theme_mod('rvx_reviews_overview_rating_out_of_total_text_color', '#BDBDBD');
        $rvx_reviews_overview_rating_out_of_total_text_font_size = get_theme_mod('rvx_reviews_overview_rating_out_of_total_text_font_size', 24);
        // Rating Badge
        $rvx_reviews_overview_rating_badge_background_color = get_theme_mod('rvx_reviews_overview_rating_badge_background_color', '#22C55E');
        $rvx_reviews_overview_rating_badge_text_color = get_theme_mod('rvx_reviews_overview_rating_badge_text_color', '#FFFFFF');
        // Total Reviews Count
        $rvx_reviews_overview_total_reviews_text_color = get_theme_mod('rvx_reviews_overview_total_reviews_text_color', '#424242');
        $rvx_reviews_overview_total_reviews_text_font_size = get_theme_mod('rvx_reviews_overview_total_reviews_text_font_size', 16);
        // Rating Overview Chart
        $rvx_reviews_overview_rating_overview_chart_text_color = get_theme_mod('rvx_reviews_overview_rating_overview_chart_text_color', '#424242');
        $rvx_reviews_overview_rating_overview_chart_text_font_size = get_theme_mod('rvx_reviews_overview_rating_overview_chart_text_font_size', 14);
        // Product Recommendation Text
        $rvx_reviews_overview_product_recommendation_background_color = get_theme_mod('rvx_reviews_overview_product_recommendation_background_color', '#F5F5F5');
        $rvx_reviews_overview_product_recommendation_border_color = get_theme_mod('rvx_reviews_overview_product_recommendation_border_color', '#F5F5F5');
        $rvx_reviews_overview_product_recommendation_border_radius = get_theme_mod('rvx_reviews_overview_product_recommendation_border_radius', 4);
        $rvx_reviews_overview_product_recommendation_text_color = get_theme_mod('rvx_reviews_overview_product_recommendation_text_color', '#424242');
        $rvx_reviews_overview_product_recommendation_text_font_size = get_theme_mod('rvx_reviews_overview_product_recommendation_text_font_size', 14);
        // Review Criteria Text
        $rvx_reviews_overview_review_criteria_text_color = get_theme_mod('rvx_reviews_overview_review_criteria_text_color', '#424242');
        $rvx_reviews_overview_review_criteria_text_font_size = get_theme_mod('rvx_reviews_overview_review_criteria_text_font_size', 14);
        /*
         * ReviewX - Filter Buttons
         */
        // Filter Button
        $rvx_filter_button_text_color = get_theme_mod('rvx_filter_button_text_color', '#424242');
        $rvx_filter_button_background_color = get_theme_mod('rvx_filter_button_background_color', '#F0F0F1');
        $rvx_filter_button_border_color = get_theme_mod('rvx_filter_button_border_color', '#BDBDBD');
        $rvx_filter_button_border_radius = get_theme_mod('rvx_filter_button_border_radius', 4);
        // Filter Dropdown menu
        $rvx_filter_dropdown_menu_text_color = get_theme_mod('rvx_filter_dropdown_menu_text_color', '#616161');
        $rvx_filter_dropdown_menu_background_color = get_theme_mod('rvx_filter_dropdown_menu_background_color', '#FFFFFF');
        $rvx_filter_dropdown_menu_border_color = get_theme_mod('rvx_filter_dropdown_menu_border_color', '#FFFFFF');
        $rvx_filter_dropdown_menu_border_radius = get_theme_mod('rvx_filter_dropdown_menu_border_radius', 4);
        $rvx_filter_dropdown_menu_text_font_size = get_theme_mod('rvx_filter_dropdown_menu_text_font_size', 14);
        // Filter Dropdown [Filter by]
        $rvx_filter_by_text_color = get_theme_mod('rvx_filter_by_text_color', '#424242');
        $rvx_filter_by_text_font_size = get_theme_mod('rvx_filter_by_text_font_size', 16);
        // Filter Dropdown menu: [Reset filters] button
        $rvx_filter_reset_button_text_color = get_theme_mod('rvx_filter_reset_button_text_color', '#0043DD');
        $rvx_filter_reset_button_background_color = get_theme_mod('rvx_filter_reset_button_background_color', '#FFFFFF');
        $rvx_filter_reset_button_border_color = get_theme_mod('rvx_filter_reset_button_border_color', '#FFFFFF');
        $rvx_filter_reset_button_border_radius = get_theme_mod('rvx_filter_reset_button_border_radius', 4);
        // Filter Dropdown menu: [Apply] button
        $rvx_filter_apply_button_text_color = get_theme_mod('rvx_filter_apply_button_text_color', '#FFFFFF');
        $rvx_filter_apply_button_background_color = get_theme_mod('rvx_filter_apply_button_background_color', '#0043DD');
        $rvx_filter_apply_button_border_color = get_theme_mod('rvx_filter_apply_button_border_color', '#0043DD');
        $rvx_filter_apply_button_border_radius = get_theme_mod('rvx_filter_apply_button_border_radius', 4);
        // Sort Button
        $rvx_sort_button_text_color = get_theme_mod('rvx_sort_button_text_color', '#424242');
        $rvx_sort_button_background_color = get_theme_mod('rvx_sort_button_background_color', '#F0F0F1');
        $rvx_sort_button_border_color = get_theme_mod('rvx_sort_button_border_color', '#BDBDBD');
        $rvx_sort_button_border_radius = get_theme_mod('rvx_sort_button_border_radius', 4);
        // Sort Dropdown menu
        $rvx_sort_dropdown_menu_text_color = get_theme_mod('rvx_sort_dropdown_menu_text_color', '#616161');
        $rvx_sort_dropdown_menu_background_color = get_theme_mod('rvx_sort_dropdown_menu_background_color', '#FFFFFF');
        $rvx_sort_dropdown_menu_border_color = get_theme_mod('rvx_sort_dropdown_menu_border_color', '#FFFFFF');
        $rvx_sort_dropdown_menu_border_radius = get_theme_mod('rvx_sort_dropdown_menu_border_radius', 4);
        $rvx_sort_dropdown_menu_text_font_size = get_theme_mod('rvx_sort_dropdown_menu_text_font_size', 14);
        // Write a Review Button
        //$rvx_write_review_button_text_color = get_theme_mod('rvx_write_review_button_text_color', '#424242');
        //$rvx_write_review_button_background_color = get_theme_mod( 'rvx_write_review_button_background_color', '#BDBDBD');
        $rvx_write_review_button_border_color = get_theme_mod('rvx_write_review_button_border_color', '#0043DD');
        $rvx_write_review_button_border_radius = get_theme_mod('rvx_write_review_button_border_radius', 4);
        /*
         * ReviewX - Review Items
         */
        // Review Items: Card
        $rvx_reviews_items_card_background_color = get_theme_mod('rvx_reviews_items_card_background_color', '#F5F5F5');
        $rvx_reviews_items_card_border_color = get_theme_mod('rvx_reviews_items_card_border_color', '#F5F5F5');
        $rvx_reviews_items_card_border_radius = get_theme_mod('rvx_reviews_items_card_border_radius', 6);
        $rvx_reviews_items_card_inline_padding = get_theme_mod('rvx_reviews_items_card_inline_padding', 8);
        // Review Items: Reviewer Name
        $rvx_reviews_items_reviewer_name_text_color = get_theme_mod('rvx_reviews_items_reviewer_name_text_color', '#424242');
        $rvx_reviews_items_reviewer_name_text_font_size = get_theme_mod('rvx_reviews_items_reviewer_name_text_font_size', 20);
        // Review Items: Review Title
        $rvx_reviews_items_title_text_color = get_theme_mod('rvx_reviews_items_title_text_color', '#424242');
        $rvx_reviews_items_title_text_font_size = get_theme_mod('rvx_reviews_items_title_text_font_size', 20);
        // Review Items: Review Date
        $rvx_reviews_items_date_text_color = get_theme_mod('rvx_reviews_items_date_text_color', '#757575');
        $rvx_reviews_items_date_text_font_size = get_theme_mod('rvx_reviews_items_date_text_font_size', 14);
        // Review Items: Description
        $rvx_reviews_items_description_text_color = get_theme_mod('rvx_reviews_items_description_text_color', '#757575');
        $rvx_reviews_items_description_text_font_size = get_theme_mod('rvx_reviews_items_description_text_font_size', 14);
        /*
         * ReviewX - Review Form
         */
        // Form
        $rvx_input_form_background_color = get_theme_mod('rvx_input_form_background_color', '#F5F5F5');
        // Disabled - already available in ReviewX-> Widget Settings
        $rvx_input_form_border_color = get_theme_mod('rvx_input_form_border_color', '#F5F5F5');
        $rvx_input_form_border_radius = get_theme_mod('rvx_input_form_border_radius', 6);
        // Form: Title
        $rvx_input_form_title_text_color = get_theme_mod('rvx_input_form_title_text_color', '#424242');
        $rvx_input_form_title_text_font_size = get_theme_mod('rvx_input_form_title_text_font_size', 18);
        // Form: Product Name
        $rvx_input_form_product_name_text_color = get_theme_mod('rvx_input_form_product_name_text_color', '#424242');
        $rvx_input_form_product_name_text_font_size = get_theme_mod('rvx_input_form_product_name_text_font_size', 18);
        // Form: Criteria Title
        $rvx_input_form_criteria_title_text_color = get_theme_mod('rvx_input_form_criteria_title_text_color', '#424242');
        $rvx_input_form_criteria_title_text_font_size = get_theme_mod('rvx_input_form_criteria_title_text_font_size', 16);
        // Form: Review Title
        $rvx_input_form_review_title_text_color = get_theme_mod('rvx_input_form_review_title_text_color', '#424242');
        $rvx_input_form_review_title_text_font_size = get_theme_mod('rvx_input_form_review_title_text_font_size', 16);
        // Form: Description Title
        $rvx_input_form_description_title_text_color = get_theme_mod('rvx_input_form_description_title_text_color', '#424242');
        $rvx_input_form_description_title_text_font_size = get_theme_mod('rvx_input_form_description_title_text_font_size', 16);
        // Form: Full Name
        $rvx_input_form_full_name_text_color = get_theme_mod('rvx_input_form_full_name_text_color', '#424242');
        $rvx_input_form_full_name_text_font_size = get_theme_mod('rvx_input_form_full_name_text_font_size', 16);
        // Form: Email Address
        $rvx_input_form_email_address_text_color = get_theme_mod('rvx_input_form_email_address_text_color', '#424242');
        $rvx_input_form_email_address_text_font_size = get_theme_mod('rvx_input_form_email_address_text_font_size', 16);
        // Form: Attachment Title
        $rvx_input_form_attachment_title_text_color = get_theme_mod('rvx_input_form_attachment_title_text_color', '#424242');
        $rvx_input_form_attachment_title_text_font_size = get_theme_mod('rvx_input_form_attachment_title_text_font_size', 16);
        // Form: Mark as Anonymous
        $rvx_input_form_mark_anonymous_text_color = get_theme_mod('rvx_input_form_mark_anonymous_text_color', '#424242');
        $rvx_input_form_mark_anonymous_text_font_size = get_theme_mod('rvx_input_form_mark_anonymous_text_font_size', 16);
        // Form: Recommended?
        $rvx_input_form_recommended_text_color = get_theme_mod('rvx_input_form_recommended_text_color', '#424242');
        $rvx_input_form_recommended_text_font_size = get_theme_mod('rvx_input_form_recommended_text_font_size', 16);
        // Form: Submit Review Button
        //$rvx_submit_review_button_text_color = get_theme_mod('rvx_submit_review_button_text_color', '#FFFFFF');
        //$rvx_submit_review_button_background_color = get_theme_mod( 'rvx_submit_review_button_background_color', '#0043DD');
        $rvx_submit_review_button_border_color = get_theme_mod('rvx_submit_review_button_border_color', '#0043DD');
        $rvx_submit_review_button_border_radius = get_theme_mod('rvx_submit_review_button_border_radius', 6);
        ?>

        <style type="text/css">

            /*
             * ReviewX - Reviews Overview
             */
            #rvx-storefront-widget p.rvx-rating-out-of,
            p.rvx-rating-out-of{
                color: <?php 
        echo esc_attr($rvx_reviews_overview_rating_out_of_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_reviews_overview_rating_out_of_text_font_size);
        ?>px !important;
            }

            #rvx-storefront-widget span.rvx-rating-total,
            span.rvx-rating-total{
                color: <?php 
        echo esc_attr($rvx_reviews_overview_rating_out_of_total_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_reviews_overview_rating_out_of_total_text_font_size);
        ?>px !important;
            }

            #rvx-storefront-widget .rvx-rating-badge,
            .rvx-rating-badge{
                background-color: <?php 
        echo esc_attr($rvx_reviews_overview_rating_badge_background_color);
        ?> !important;
                color: <?php 
        echo esc_attr($rvx_reviews_overview_rating_badge_text_color);
        ?> !important;
            }

            /*
            #rvx-storefront-widget .rvx-review-form__star-active,
            .rvx-review-form__star-active{
                fill:<?php 
        //echo esc_attr($rvx_general_reviews_active_rating_stars_background_color);
        ?>;
            }
            #rvx-storefront-widget .rvx-review-form__star-active-half-star,
            .rvx-review-form__star-active-half-star{
                stop-color:<?php 
        //echo esc_attr($rvx_general_reviews_active_rating_stars_background_color);
        ?>;
            }
            #rvx-storefront-widget .rvx-aggregation__rating-icon path,
            .rvx-aggregation__rating-icon path{
                fill:<?php 
        //echo esc_attr($rvx_general_reviews_active_rating_stars_background_color);
        ?>;
            }
            #rvx-storefront-widget .rvx-aggregation__rating-icon,
            .rvx-aggregation__rating-icon{
                fill:<?php 
        //echo esc_attr($rvx_general_reviews_active_rating_stars_background_color);
        ?>;
            }
            */
            
            #rvx-storefront-widget p.rvx-total-review,
            p.rvx-total-review{
                color: <?php 
        echo esc_attr($rvx_reviews_overview_total_reviews_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_reviews_overview_total_reviews_text_font_size);
        ?>px !important;
            }

            #rvx-storefront-widget tr.rvx-aggregation__row td span,
            tr.rvx-aggregation__row td span{
                color: <?php 
        echo esc_attr($rvx_reviews_overview_rating_overview_chart_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_reviews_overview_rating_overview_chart_text_font_size);
        ?>px !important;
            }
            #rvx-storefront-widget .rvx-recommendation-count,
            .rvx-recommendation-count{
                background-color: <?php 
        echo esc_attr($rvx_reviews_overview_product_recommendation_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo esc_attr($rvx_reviews_overview_product_recommendation_border_color);
        ?> !important;
                border-radius: <?php 
        echo esc_attr($rvx_reviews_overview_product_recommendation_border_radius);
        ?>px !important;
            }
            #rvx-storefront-widget .rvx-recommendation-count p,
            .rvx-recommendation-count p{
                color: <?php 
        echo esc_attr($rvx_reviews_overview_product_recommendation_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_reviews_overview_product_recommendation_text_font_size);
        ?>px !important;
            }

            #rvx-storefront-widget .rvx-recommendation-count svg,
            .rvx-recommendation-count svg{
                width:<?php 
        echo esc_attr($rvx_reviews_overview_product_recommendation_text_font_size);
        ?>;
                height:<?php 
        echo esc_attr($rvx_reviews_overview_product_recommendation_text_font_size);
        ?>;
            }
            
            #rvx-storefront-widget .rvx-aggregation-multicriteria span,
            .rvx-aggregation-multicriteria span{
                color: <?php 
        echo esc_attr($rvx_reviews_overview_review_criteria_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_reviews_overview_review_criteria_text_font_size);
        ?>px !important;
            }


            /*
             * ReviewX - Filter Buttons
             */
            #rvx-storefront-widget .rvx-review-filter__button,
            .rvx-review-filter__button{
                color: <?php 
        echo esc_attr($rvx_filter_button_text_color);
        ?> !important;
                background-color: <?php 
        echo esc_attr($rvx_filter_button_background_color);
        ?> !important;
            }
            #rvx-storefront-widget .rvx-review-filter__button,
            .rvx-review-filter__button{
                border:solid 1px <?php 
        echo esc_attr($rvx_filter_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo esc_attr($rvx_filter_button_border_radius);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-filter-wrapper,
            .rvx-review-filter-wrapper{
                color: <?php 
        echo esc_attr($rvx_filter_dropdown_menu_text_color);
        ?> !important;
                background-color: <?php 
        echo esc_attr($rvx_filter_dropdown_menu_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo esc_attr($rvx_filter_dropdown_menu_border_color);
        ?> !important;
                border-radius: <?php 
        echo esc_attr($rvx_filter_dropdown_menu_border_radius);
        ?>px !important;
                font-size: <?php 
        echo esc_attr($rvx_filter_dropdown_menu_text_font_size);
        ?>px !important;
            }

            #rvx-storefront-widget .rvx-review-filter-wrapper p.rvx-review-filter-wrapper__title,
            .rvx-review-filter-wrapper p.rvx-review-filter-wrapper__title{
                color: <?php 
        echo esc_attr($rvx_filter_by_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_filter_by_text_font_size);
        ?>px !important;
            }

            #rvx-storefront-widget .rvx-review-filter-wrapper__footer-reset-button,
            .rvx-review-filter-wrapper__footer-reset-button{
                color: <?php 
        echo esc_attr($rvx_filter_reset_button_text_color);
        ?> !important;
                background-color: <?php 
        echo esc_attr($rvx_filter_reset_button_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo esc_attr($rvx_filter_reset_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo esc_attr($rvx_filter_reset_button_border_radius);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-filter-wrapper__footer-save-button,
            .rvx-review-filter-wrapper__footer-save-button{
                color: <?php 
        echo esc_attr($rvx_filter_apply_button_text_color);
        ?> !important;
                background-color: <?php 
        echo esc_attr($rvx_filter_apply_button_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo esc_attr($rvx_filter_apply_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo esc_attr($rvx_filter_apply_button_border_radius);
        ?>px !important;
            }
            

            #rvx-storefront-widget .rvx-review-sort__button,
            .rvx-review-sort__button{
                color: <?php 
        echo esc_attr($rvx_sort_button_text_color);
        ?> !important;
                background-color: <?php 
        echo esc_attr($rvx_sort_button_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo esc_attr($rvx_sort_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo esc_attr($rvx_sort_button_border_radius);
        ?>px !important;
            }

            #rvx-storefront-widget .rvx-review-sort-wrapper__outer,
            .rvx-review-sort-wrapper__outer{
                color: <?php 
        echo esc_attr($rvx_sort_dropdown_menu_text_color);
        ?> !important;
                background-color: <?php 
        echo esc_attr($rvx_sort_dropdown_menu_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo esc_attr($rvx_sort_dropdown_menu_border_color);
        ?> !important;
                border-radius: <?php 
        echo esc_attr($rvx_sort_dropdown_menu_border_radius);
        ?>px !important;
                font-size: <?php 
        echo esc_attr($rvx_sort_dropdown_menu_text_font_size);
        ?>px !important;
            }

            #rvx-storefront-widget .rvx-review-write__button,
            .rvx-review-write__button{
                border:solid 1px <?php 
        echo esc_attr($rvx_write_review_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo esc_attr($rvx_write_review_button_border_radius);
        ?>px !important;
            }

            /*
             * ReviewX - Review Items
             */
            #rvx-storefront-widget .rvx-review-wrapper .rvx-review-card,
            .rvx-review-wrapper .rvx-review-card{
                background-color: <?php 
        echo esc_attr($rvx_reviews_items_card_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo esc_attr($rvx_reviews_items_card_border_color);
        ?> !important;
                border-radius: <?php 
        echo esc_attr($rvx_reviews_items_card_border_radius);
        ?>px !important;
                padding: <?php 
        echo esc_attr($rvx_reviews_items_card_inline_padding);
        ?>px !important;
            }

            #rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-user__name,
            .rvx-review-wrapper .rvx-review-card .rvx-review-user__name{
                color: <?php 
        echo esc_attr($rvx_reviews_items_reviewer_name_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_reviews_items_reviewer_name_text_font_size);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-info__title,
            .rvx-review-wrapper .rvx-review-card .rvx-review-info__title{
                color: <?php 
        echo esc_attr($rvx_reviews_items_title_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_reviews_items_title_text_font_size);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-info__date,
            .rvx-review-wrapper .rvx-review-card .rvx-review-info__date{
                color: <?php 
        echo esc_attr($rvx_reviews_items_date_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_reviews_items_date_text_font_size);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-info__feedback,
            .rvx-review-wrapper .rvx-review-card .rvx-review-info__feedback{
                color: <?php 
        echo esc_attr($rvx_reviews_items_description_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_reviews_items_description_text_font_size);
        ?>px !important;
            }
            
            
            /*
             * ReviewX - Review Form
             */
            #rvx-storefront-widget #rvx-review-form__wrapper,
            #rvx-review-form__wrapper{
                background-color: <?php 
        echo esc_attr($rvx_input_form_background_color);
        ?> !important;
                border:solid 1px <?php 
        echo esc_attr($rvx_input_form_border_color);
        ?> !important;
                border-radius: <?php 
        echo esc_attr($rvx_input_form_border_radius);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-form__title,
            .rvx-review-form__title{
                color: <?php 
        echo esc_attr($rvx_input_form_title_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_input_form_title_text_font_size);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-form__product--title,
            .rvx-review-form__product--title{
                color: <?php 
        echo esc_attr($rvx_input_form_product_name_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_input_form_product_name_text_font_size);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-form__multicriteria--name,
            .rvx-review-form__multicriteria--name{
                color: <?php 
        echo esc_attr($rvx_input_form_criteria_title_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_input_form_criteria_title_text_font_size);
        ?>px !important;
            }

            #rvx-storefront-widget .rvx-review-form__title--name,
            .rvx-review-form__title--name{
                color: <?php 
        echo esc_attr($rvx_input_form_review_title_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_input_form_review_title_text_font_size);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-form__description-title,
            .rvx-review-form__description-title{
                color: <?php 
        echo esc_attr($rvx_input_form_description_title_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_input_form_description_title_text_font_size);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-form__user--name,
            .rvx-review-form__user--name{
                color: <?php 
        echo esc_attr($rvx_input_form_full_name_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_input_form_full_name_text_font_size);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-form__email--name,
            .rvx-review-form__email--name{
                color: <?php 
        echo esc_attr($rvx_input_form_email_address_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_input_form_email_address_text_font_size);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-form__attachment--name,
            .rvx-review-form__attachment--name{
                color: <?php 
        echo esc_attr($rvx_input_form_attachment_title_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_input_form_attachment_title_text_font_size);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-form__mark-anonymous,
            .rvx-review-form__mark-anonymous{
                color: <?php 
        echo esc_attr($rvx_input_form_mark_anonymous_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_input_form_mark_anonymous_text_font_size);
        ?>px !important;
            }
            
            #rvx-storefront-widget .rvx-review-form__recommended--name,
            .rvx-review-form__recommended--name{
                color: <?php 
        echo esc_attr($rvx_input_form_recommended_text_color);
        ?> !important;
                font-size: <?php 
        echo esc_attr($rvx_input_form_recommended_text_font_size);
        ?>px !important;
            }

            #rvx-storefront-widget .rvx-review-form__submit--button,
            .rvx-review-form__submit--button{
                border:solid 1px <?php 
        echo esc_attr($rvx_submit_review_button_border_color);
        ?> !important;
                border-radius: <?php 
        echo esc_attr($rvx_submit_review_button_border_radius);
        ?>px !important;
            }
        </style>
        <?php 
    }
}

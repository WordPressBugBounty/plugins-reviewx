(function($) {

    // Live style updater
    function updateStyle(selector, property, value) {
        var $element = $(selector);
        var currentStyle = $element.attr('style') || '';
        var styles = currentStyle.split(';').filter(Boolean);
        var updated = false;
    
        styles = styles.map(function(style) {
            var [prop, val] = style.split(':');
            if (prop.trim() === property) {
                updated = true;
                return property + ': ' + value + ' !important';
            }
            return style;
        });
    
        if (!updated) {
            styles.push(property + ': ' + value + ' !important');
        }
    
        $element.attr('style', styles.join('; '));
    }
    
    /*
     *  ReviewX - General Settings
     */

    // Active Rating Stars: [Background Color]
    /*
    wp.customize('rvx_general_reviews_active_rating_stars_background_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__star-active, .rvx-review-form__star-active', 'fill', newval);
            updateStyle('#rvx-storefront-widget .rvx-review-form__star-active-half-star, .rvx-review-form__star-active-half-star', 'stop-color', newval);
            updateStyle('#rvx-storefront-widget .rvx-aggregation__rating-icon, .rvx-aggregation__rating-icon', 'fill', newval);
            updateStyle('#rvx-storefront-widget .rvx-aggregation__rating-icon path, .rvx-aggregation__rating-icon path', 'fill', newval);
        });
    });
    */

    /*
     *  ReviewX - Reviews Overview
     */

    // Rating out of
    wp.customize('rvx_reviews_overview_rating_out_of_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget p.rvx-rating-out-of, p.rvx-rating-out-of', 'color', newval);
        });
    });
    wp.customize('rvx_reviews_overview_rating_out_of_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget p.rvx-rating-out-of, p.rvx-rating-out-of', 'font-size', newval + 'px');
        });
    });

    // Rating out of Total
    wp.customize('rvx_reviews_overview_rating_out_of_total_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget span.rvx-rating-total, span.rvx-rating-total', 'color', newval);
        });
    });
    wp.customize('rvx_reviews_overview_rating_out_of_total_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget span.rvx-rating-total, span.rvx-rating-total', 'font-size', newval + 'px');
        });
    });

    // Rating Badge
    wp.customize('rvx_reviews_overview_rating_badge_background_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-rating-badge, .rvx-rating-badge', 'background-color', newval);
        });
    });
    wp.customize('rvx_reviews_overview_rating_badge_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-rating-badge span, .rvx-rating-badge span', 'color', newval);
        });
    });


    // Total Reviews Count
    wp.customize('rvx_reviews_overview_total_reviews_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget p.rvx-total-review, p.rvx-total-review', 'color', newval);
        });
    });
    wp.customize('rvx_reviews_overview_total_reviews_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget p.rvx-total-review, p.rvx-total-review', 'font-size', newval + 'px');
        });
    });


    // Rating Overview Chart
    wp.customize('rvx_reviews_overview_rating_overview_chart_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget tr.rvx-aggregation__row td span, tr.rvx-aggregation__row td span', 'color', newval);
        });
    });
    wp.customize('rvx_reviews_overview_rating_overview_chart_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget tr.rvx-aggregation__row td span, tr.rvx-aggregation__row td span', 'font-size', newval + 'px');
        });
    });


    // Product Recommendation Text
    wp.customize('rvx_reviews_overview_product_recommendation_background_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-recommendation-count, .rvx-recommendation-count', 'background-color', newval);
        });
    });
    wp.customize('rvx_reviews_overview_product_recommendation_border_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-recommendation-count, .rvx-recommendation-count', 'border', 'solid 1px ' + newval);
        });
    });
    wp.customize('rvx_reviews_overview_product_recommendation_border_radius', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-recommendation-count, .rvx-recommendation-count', 'border-radius', newval + 'px');
        });
    });
    wp.customize('rvx_reviews_overview_product_recommendation_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-recommendation-count p, .rvx-recommendation-count p', 'color', newval);
        });
    });
    wp.customize('rvx_reviews_overview_product_recommendation_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-recommendation-count p, .rvx-recommendation-count p', 'font-size', newval + 'px');
        });
    });

    // Product Recommendation Icon
    wp.customize('rvx_reviews_overview_product_recommendation_text_font_size', function(value) {
        value.bind(function(newval) {
            $('#rvx-storefront-widget .rvx-recommendation-count svg, .rvx-recommendation-count svg').attr('width', newval);
        });
    });
    wp.customize('rvx_reviews_overview_product_recommendation_text_font_size', function(value) {
        value.bind(function(newval) {
            $('#rvx-storefront-widget .rvx-recommendation-count svg, .rvx-recommendation-count svg').attr('height', newval);
        });
    });


    // Review Criteria Text
    wp.customize('rvx_reviews_overview_review_criteria_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-aggregation-multicriteria span, .rvx-aggregation-multicriteria span', 'color', newval);
        });
    });
    wp.customize('rvx_reviews_overview_review_criteria_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-aggregation-multicriteria span, .rvx-aggregation-multicriteria span', 'font-size', newval + 'px');
        });
    });

    /*
     *  ReviewX - Filter
     */

    // Filter button
    wp.customize('rvx_filter_button_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter__button, .rvx-review-filter__button', 'color', newval);
        });
    });
    wp.customize('rvx_filter_button_background_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter__button, .rvx-review-filter__button', 'background-color', newval);
        });
    });
    wp.customize('rvx_filter_button_border_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter__button, .rvx-review-filter__button', 'border', 'solid 1px ' + newval);
        });
    });
    wp.customize('rvx_filter_button_border_radius', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter__button, .rvx-review-filter__button', 'border-radius', newval + 'px');
        });
    });
    
    // Filter dropdown - Filter by
    wp.customize('rvx_filter_by_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper p.rvx-review-filter-wrapper__title, .rvx-review-filter-wrapper p.rvx-review-filter-wrapper__title', 'color', newval);
        });
    });
    wp.customize('rvx_filter_by_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper p.rvx-review-filter-wrapper__title, .rvx-review-filter-wrapper p.rvx-review-filter-wrapper__title', 'font-size', newval + 'px');
        });
    });

    // Filter Dropdown menu
    wp.customize('rvx_filter_dropdown_menu_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper, .rvx-review-filter-wrapper', 'color', newval);
        });
    });
    wp.customize('rvx_filter_dropdown_menu_background_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper, .rvx-review-filter-wrapper', 'background-color', newval);
        });
    });
    wp.customize('rvx_filter_dropdown_menu_border_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper, .rvx-review-filter-wrapper', 'border', 'solid 1px ' + newval);
        });
    });
    wp.customize('rvx_filter_dropdown_menu_border_radius', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper, .rvx-review-filter-wrapper', 'border-radius', newval + 'px');
        });
    });
    wp.customize('rvx_filter_dropdown_menu_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper, .rvx-review-filter-wrapper', 'font-size', newval + 'px');
        });
    });

    // Filter Dropdown menu: [Rest filters] button
    wp.customize('rvx_filter_reset_button_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper__footer-reset-button, .rvx-review-filter-wrapper__footer-reset-button', 'color', newval);
        });
    });
    wp.customize('rvx_filter_reset_button_background_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper__footer-reset-button, .rvx-review-filter-wrapper__footer-reset-button', 'background-color', newval);
        });
    });
    wp.customize('rvx_filter_reset_button_border_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper__footer-reset-button, .rvx-review-filter-wrapper__footer-reset-button', 'border', 'solid 1px ' + newval);
        });
    });
    wp.customize('rvx_filter_reset_button_border_radius', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper__footer-reset-button, .rvx-review-filter-wrapper__footer-reset-button', 'border-radius', newval + 'px');
        });
    });
    
    // Filter Dropdown menu: [Apply] button
    wp.customize('rvx_filter_apply_button_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper__footer-save-button, .rvx-review-filter-wrapper__footer-save-button', 'color', newval);
        });
    });
    wp.customize('rvx_filter_apply_button_background_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper__footer-save-button, .rvx-review-filter-wrapper__footer-save-button', 'background-color', newval);
        });
    });
    wp.customize('rvx_filter_apply_button_border_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper__footer-save-button, .rvx-review-filter-wrapper__footer-save-button', 'border', 'solid 1px ' + newval);
        });
    });
    wp.customize('rvx_filter_apply_button_border_radius', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-filter-wrapper__footer-save-button, .rvx-review-filter-wrapper__footer-save-button', 'border-radius', newval + 'px');
        });
    });

    // Sort button
    wp.customize('rvx_sort_button_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-sort__button, .rvx-review-sort__button', 'color', newval);
        });
    });
    wp.customize('rvx_sort_button_background_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-sort__button, .rvx-review-sort__button', 'background-color', newval);
        });
    });
    wp.customize('rvx_sort_button_border_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-sort__button, .rvx-review-sort__button', 'border', 'solid 1px ' + newval);
        });
    });
    wp.customize('rvx_sort_button_border_radius', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-sort__button, .rvx-review-sort__button', 'border-radius', newval + 'px');
        });
    });
    
    // Sort Dropdown menu
    wp.customize('rvx_sort_dropdown_menu_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-sort-wrapper__outer, .rvx-review-sort-wrapper__outer', 'color', newval);
        });
    });
    wp.customize('rvx_sort_dropdown_menu_background_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-sort-wrapper__outer, .rvx-review-sort-wrapper__outer', 'background-color', newval);
        });
    });
    wp.customize('rvx_sort_dropdown_menu_border_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-sort-wrapper__outer, .rvx-review-sort-wrapper__outer', 'border', 'solid 1px ' + newval);
        });
    });
    wp.customize('rvx_sort_dropdown_menu_border_radius', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-sort-wrapper__outer, .rvx-review-sort-wrapper__outer', 'border-radius', newval + 'px');
        });
    });
    wp.customize('rvx_sort_dropdown_menu_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-sort-wrapper__outer, .rvx-review-sort-wrapper__outer', 'font-size', newval + 'px');
        });
    });

    // Write a Review button
    wp.customize('rvx_write_review_button_border_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-write__button, .rvx-review-write__button', 'border', 'solid 1px ' + newval);
        });
    });
    wp.customize('rvx_write_review_button_border_radius', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-write__button, .rvx-review-write__button', 'border-radius', newval + 'px');
        });
    });
    

    /*
     *  ReviewX - Review Items
     */
    
    // Review Items: Card [Background Color]
    wp.customize('rvx_reviews_items_card_background_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card, .rvx-review-wrapper .rvx-review-card', 'background-color', newval);
        });
    });
    // Review Items: Card [Border Color]
    wp.customize('rvx_reviews_items_card_border_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card, .rvx-review-wrapper .rvx-review-card', 'border', 'solid 1px ' + newval);
        });
    });
    // Review Items: Card [Border Radius]
    wp.customize('rvx_reviews_items_card_border_radius', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card, .rvx-review-wrapper .rvx-review-card', 'border-radius', newval + 'px');
        });
    });
    // Review Items: Card [Padding]
    wp.customize('rvx_reviews_items_card_inline_padding', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card, .rvx-review-wrapper .rvx-review-card', 'padding', newval + 'px');
        });
    });

    // Review Items: Reviewer Name
    wp.customize('rvx_reviews_items_reviewer_name_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-user__name, .rvx-review-wrapper .rvx-review-card .rvx-review-user__name', 'color', newval);
        });
    });
    wp.customize('rvx_reviews_items_reviewer_name_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-user__name, .rvx-review-wrapper .rvx-review-card .rvx-review-user__name', 'font-size', newval + 'px');
        });
    });

    // Review Items: Review Title
    wp.customize('rvx_reviews_items_title_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-info__title, .rvx-review-wrapper .rvx-review-card .rvx-review-info__title', 'color', newval);
        });
    });
    wp.customize('rvx_reviews_items_title_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-info__title, .rvx-review-wrapper .rvx-review-card .rvx-review-info__title', 'font-size', newval + 'px');
        });
    });
    
    // Review Items: Review Date
    wp.customize('rvx_reviews_items_date_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-info__date, .rvx-review-wrapper .rvx-review-card .rvx-review-info__date', 'color', newval);
        });
    });
    wp.customize('rvx_reviews_items_date_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-info__date, .rvx-review-wrapper .rvx-review-card .rvx-review-info__date', 'font-size', newval + 'px');
        });
    });
    
    // Review Items: Description
    wp.customize('rvx_reviews_items_description_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-info__feedback, .rvx-review-wrapper .rvx-review-card .rvx-review-info__feedback', 'color', newval);
        });
    });
    wp.customize('rvx_reviews_items_description_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-wrapper .rvx-review-card .rvx-review-info__feedback, .rvx-review-wrapper .rvx-review-card .rvx-review-info__feedback', 'font-size', newval + 'px');
        });
    });


    /*
     *  ReviewX - Review Form
     */
    
    // Form: Background Color
    wp.customize('rvx_input_form_background_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget #rvx-review-form__wrapper, #rvx-review-form__wrapper', 'background-color', newval);
        });
    });
    
    // Form: Border Color
    wp.customize('rvx_input_form_border_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget #rvx-review-form__wrapper, #rvx-review-form__wrapper', 'border', 'solid 1px ' + newval);
        });
    });
    // Form: Border Radius
    wp.customize('rvx_input_form_border_radius', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget #rvx-review-form__wrapper, #rvx-review-form__wrapper', 'border-radius', newval + 'px');
        });
    });


    // Form: Title
    wp.customize('rvx_input_form_title_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__title, .rvx-review-form__title', 'color', newval);
        });
    });
    wp.customize('rvx_input_form_title_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__title, .rvx-review-form__title', 'font-size', newval + 'px');
        });
    });

    // Form: Product Name
    wp.customize('rvx_input_form_product_name_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__product--title, .rvx-review-form__product--title', 'color', newval);
        });
    });
    wp.customize('rvx_input_form_product_name_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__product--title, .rvx-review-form__product--title', 'font-size', newval + 'px');
        });
    });

    // Form: Criteria Title
    wp.customize('rvx_input_form_criteria_title_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__multicriteria--name, .rvx-review-form__multicriteria--name', 'color', newval);
        });
    });
    wp.customize('rvx_input_form_criteria_title_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__multicriteria--name, .rvx-review-form__multicriteria--name', 'font-size', newval + 'px');
        });
    });

    // Form: Review Title
    wp.customize('rvx_input_form_review_title_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__title--name, .rvx-review-form__title--name', 'color', newval);
        });
    });
    wp.customize('rvx_input_form_review_title_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__title--name, .rvx-review-form__title--name', 'font-size', newval + 'px');
        });
    });

    // Form: Description Title
    wp.customize('rvx_input_form_description_title_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__description--title, .rvx-review-form__description--title', 'color', newval);
        });
    });
    wp.customize('rvx_input_form_description_title_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__description--title, .rvx-review-form__description--title', 'font-size', newval + 'px');
        });
    });

    // Form: Full Name
    wp.customize('rvx_input_form_full_name_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__user--name, .rvx-review-form__user--name', 'color', newval);
        });
    });
    wp.customize('rvx_input_form_full_name_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__user--name, .rvx-review-form__user--name', 'font-size', newval + 'px');
        });
    });

    // Form: Email Address
    wp.customize('rvx_input_form_email_address_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__email--name, .rvx-review-form__email--name', 'color', newval);
        });
    });
    wp.customize('rvx_input_form_email_address_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__email--name, .rvx-review-form__email--name', 'font-size', newval + 'px');
        });
    });

    // Form: Attachment Title
    wp.customize('rvx_input_form_attachment_title_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__attachment--name, .rvx-review-form__attachment--name', 'color', newval);
        });
    });
    wp.customize('rvx_input_form_attachment_title_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__attachment--name, .rvx-review-form__attachment--name', 'font-size', newval + 'px');
        });
    });

    // Form: Mark as Anonymous
    wp.customize('rvx_input_form_mark_anonymous_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__mark-anonymous, .rvx-review-form__mark-anonymous', 'color', newval);
        });
    });
    wp.customize('rvx_input_form_mark_anonymous_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__mark-anonymous, .rvx-review-form__mark-anonymous', 'font-size', newval + 'px');
        });
    });

    // Form: Recommended?
    wp.customize('rvx_input_form_recommended_text_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__recommended--name, .rvx-review-form__recommended--name', 'color', newval);
        });
    });
    wp.customize('rvx_input_form_recommended_text_font_size', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__recommended--name, .rvx-review-form__recommended--name', 'font-size', newval + 'px');
        });
    });

    // Form: Submit Review Button
    wp.customize('rvx_submit_review_button_border_color', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__submit--button, .rvx-review-form__submit--button', 'border', 'solid 1px ' + newval);
        });
    });
    wp.customize('rvx_submit_review_button_border_radius', function(value) {
        value.bind(function(newval) {
            updateStyle('#rvx-storefront-widget .rvx-review-form__submit--button, .rvx-review-form__submit--button', 'border-radius', newval + 'px');
        });
    });

})(jQuery);
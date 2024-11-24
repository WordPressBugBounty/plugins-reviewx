function __rvxReviewFilterComponent__() {
    return {
        openFilterOptions: false,
        openSortOptions: false,
        selectFilterByRating: 'all',
        selectFilterByAttachment: 'both',
        selectFilterCount: 0,
        selectSortBy :'newest_first',
        filterByRatingOptions:[
            { label: 'All Rating', value: 'all' },
            { label: '4 Star & above', value: '4' },
            { label: '3 Star & above', value: '3' },
            { label: '2 Star & above', value: '2' },
            { label: '1 Star & above', value: '1' }
        ],
        filterByAttachment:[
            { label: 'With Attachment', value: 'with_attachment' },
            { label: 'Without Attachment', value: 'without_attachment' },
            { label: 'Both', value: 'both' },
        ],
        sortByOptions(){
            if(this.reviewSettingsData?.data?.setting?.widget_settings.filter_and_sort_options.sort_options.by_rating && this.reviewSettingsData?.data?.setting?.widget_settings.filter_and_sort_options.sort_options.by_time){
                return [
                    { label: 'Newest first', value: 'newest_first' },
                    { label: 'Oldest first', value: 'oldest_first' },
                    { label: 'Rating Ascending', value: 'rating_asc' },
                    { label: 'Rating Descending', value: 'rating_desc' },
                ]
            }

            if(this.reviewSettingsData?.data?.setting?.widget_settings.filter_and_sort_options.sort_options.by_rating){
                return [
                    { label: 'Rating Ascending', value: 'rating_asc' },
                    { label: 'Rating Descending', value: 'rating_desc' },
                ]
            }
            if (this.reviewSettingsData?.data?.setting?.widget_settings.filter_and_sort_options.sort_options.by_time){
                return [
                    { label: 'Newest first', value: 'newest_first' },
                    { label: 'Oldest first', value: 'oldest_first' },
                ]
            }

        },
        applyFilterHandler(){
            const newQuery = {}
            this.selectFilterCount = 0
            if (this.selectFilterByAttachment) {
                newQuery.attachment=  this.selectFilterByAttachment
                if(this.selectFilterByAttachment !== 'both'){
                    this.selectFilterCount += 1
                }
                // storeFrontReviewQueryCount.value += 1
            }
            if (this.selectFilterByRating) {
                newQuery.rating = this.selectFilterByRating
                if(this.selectFilterByRating !== 'all'){
                    this.selectFilterCount += 1
                }
                // storeFrontReviewQueryCount.value += 1
            }
            this.storeFrontReviewQuery = {
                ...this.storeFrontReviewQuery,
                ...newQuery
            }
            this.isFiltering = true
            this.fetchReviews({query: this.storeFrontReviewQuery, productId: this.rvxAttributes?.product?.id})
            this.openFilterOptions = false
        },
        filterResetHandler(){
            // Reset the selected filters to default values
            this.selectFilterByRating = 'all';
            this.selectFilterByAttachment = 'both';
            this.selectSortBy = 'newest_first';

            const newQuery = {
                attachment: this.selectFilterByAttachment,
                rating: this.selectFilterByRating,
                sortBy: this.selectSortBy
            };

            this.storeFrontReviewQuery = {
                ...this.storeFrontReviewQuery,
                ...newQuery
            };
            this.selectFilterCount = 0
            this.isFiltering = false
            // Fetch reviews based on the reset query
            this.fetchReviews({query: this.storeFrontReviewQuery, productId: this.rvxAttributes?.product?.id});

            // Close the filter options UI
            this.openFilterOptions = false;
        },
        init() {
            this.$watch('selectSortBy', (newValue, oldValue) => {
                if(newValue !== oldValue){
                    if (this.fetchReviewsIsLoading) return;
                    this.storeFrontReviewQuery = {
                        ...this.storeFrontReviewQuery,
                        sortBy: newValue
                    }
                    this.fetchReviews({query: this.storeFrontReviewQuery, productId: this.rvxAttributes?.product?.id})
                    this.openSortOptions = false
                }
            });
        },
    }
}
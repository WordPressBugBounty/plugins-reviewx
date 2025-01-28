function __rvxReviewFilterComponent__() {
    return {
        openFilterOptions: false,
        openSortOptions: false,
        selectFilterByRating: 'all',
        selectFilterByAttachment: 'both',
        selectFilterCount: 0,
        selectSortBy :'newest_first',
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
                cursor: '',
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
                cursor: '',
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
                        cursor: '',
                        sortBy: newValue
                    }
                    this.fetchReviews({query: this.storeFrontReviewQuery, productId: this.rvxAttributes?.product?.id})
                    this.openSortOptions = false
                }
            });
        },
    }
}
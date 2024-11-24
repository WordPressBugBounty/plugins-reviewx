function __rvxMultiCriteriaRatingComponent__(rating, index) {
    return {
        // selectedStars: rating.rating, // Initialize with the rating for this criterion
        // selectCriteriaStars(star) {
        //     this.selectedStars = star;
        //     rating.rating = star;
        // },

        selectedStars: 5, // Set initially selected stars
        selectCriteriaStars(star) {
            this.selectedStars = star;
            rating.rating = star;
        },
        getCriteriaStarsClass(star) {
            return [
                star <= this.selectedStars ? 'rvx-review-form__star-active' : 'rvx-fill-neutral-600 rvx-review-form__star-inactive'
            ];
        }
    }
}

function __rvxStarRatingComponent__() {
    return {
        selectedStars: 5, // Set initially selected stars
        selectStars(star) {
            this.selectedStars = star;
            this.newReview.rating = star
        },
        getStarClass(star) {
            return [
                star <= this.selectedStars ? 'rvx-review-form__star-active' : 'rvx-fill-neutral-600 rvx-review-form__star-inactive'
            ];
        }
    }
}
<?php

namespace Rvx\Handlers\Customize;

use Rvx\Utilities\Helper;
use Rvx\WPDrill\Facades\View;
class WCUserDashboardAddReview
{
    /**
     * Renders the RVX review form on the WooCommerce user dashboard.
     *
     * This outputs the review form view for the WooCommerce user dashboard.
     *
     */
    public function renderRvxReviewForm()
    {
        $attributes = self::setRvxAttributes();
        $formData = $this->formDefaultTextsData();
        View::output('storefront/my-account/review-form', ['data' => $attributes, 'formLevelData' => $formData]);
        return '';
    }
    public function setRvxAttributes()
    {
        $user_id = get_current_user_id();
        $wpCurrentUser = Helper::getWpCurrentUser();
        $user_name = $wpCurrentUser ? $wpCurrentUser->display_name : '';
        $attributes = ['userInfo' => ['isLoggedIn' => Helper::loggedIn(), 'id' => $wpCurrentUser ? $wpCurrentUser->ID : null, 'name' => $user_name, 'email' => $wpCurrentUser ? $wpCurrentUser->user_email : '', 'isVerified' => Helper::verifiedCustomer($user_id)], 'domain' => ['baseDomain' => Helper::domainSupport(), 'baseRestUrl' => Helper::getRestAPIurl()]];
        return \json_encode($attributes);
    }
    public function formDefaultTextsData()
    {
        // Define the default values, if no builder is active / available then use the default string / texts
        $default_values = ['write_a_review' => 'Write a Review', 'text_rating_star_title' => 'Rating', 'text_review_title' => 'Review Title', 'placeholder_review_title' => 'Write Review Title', 'text_review_description' => 'Description', 'placeholder_review_description' => 'Write your description here', 'text_full_name' => 'Full name', 'placeholder_full_name' => 'Full Name', 'text_email_name' => 'Email address', 'placeholder_email_name' => 'Email Address', 'text_attachment_title' => 'Attachment', 'placeholder_upload_photo' => 'Upload Photo / Video', 'text_mark_as_anonymous' => 'Mark as Anonymous', 'text_recommended_title' => 'Recommendation?'];
        return \json_encode($default_values);
    }
}

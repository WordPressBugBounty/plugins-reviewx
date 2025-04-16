<?php

namespace Rvx\Handlers\WcTemplates;

class WcEditAccountForm
{
    public function __invoke()
    {
        $user_id = get_current_user_id();
        $attachment_id = get_user_meta($user_id, "rvx_image", \true);
        if ($attachment_id) {
            $original_image_url = wp_get_attachment_url($attachment_id);
        }
        if (!empty($original_image_url)) {
            echo '<img src="' . esc_url($original_image_url) . '" width="70px" height="70px" alt="user image">';
        }
        ?>
          <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
               <label for="image"><?php 
        esc_html_e("Image", "woocommerce");
        ?></label>
               <input type="file" class="woocommerce-Input" name="image" accept="image/x-png,image/gif,image/jpeg">
            </p>   
            <?php 
    }
}

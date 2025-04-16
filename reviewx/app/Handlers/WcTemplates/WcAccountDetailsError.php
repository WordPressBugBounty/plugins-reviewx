<?php

namespace Rvx\Handlers\WcTemplates;

class WcAccountDetailsError
{
    public function __invoke($args)
    {
        // if (
        //     isset($_FILES["image"]) &&
        //     $_FILES["image"]["error"] != UPLOAD_ERR_OK
        // ) {
        //     $args->add(
        //         "image_error",
        //         __("Please provide a valid image", "woocommerce")
        //     );
        // }
    }
}

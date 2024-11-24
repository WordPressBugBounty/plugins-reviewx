<?php

namespace Rvx\Handlers\WcTemplates;

class CommentDefaultTemplateHandler
{
    public function __invoke($default)
    {
        $data = get_option('_rvx_custom_post_type_settings');
        $enabled_post_types = [];
        if ($data) {
            foreach ($data['data']['reviews'] as $review) {
                if ($review['status'] === 'Enabled') {
                    $enabled_post_types[] = $review['post_type'];
                }
            }
        }
        $enabled_post_types[] = 'product';
        if (is_singular($enabled_post_types)) {
            return \dirname(__FILE__) . '/widget.php';
        }
        return $default;
    }
}

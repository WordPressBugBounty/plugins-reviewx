<?php

namespace Rvx\Handlers\Notice;

use Rvx\WPDrill\Facades\View;
class ReviewxAdminNoticeHandler
{
    public function __invoke()
    {
        View::output('notice/notice', ['data' => $attributes, 'formLevelData' => $formData]);
        ?>
<!--        <div class="notice notice-info is-dismissible">-->
<!--            <h4>--><?php 
        //_e('Biggest Discount on ReviewX - Save 40% Now! 🔥', 'reviewx');
        ?><!--</h4>-->
<!--            <p>--><?php 
        //_e( 'Enhance reviews and boost conversions with ReviewX. Get flat 40% off now - offer ends soon!', 'reviewx' );
        ?><!--</p>-->
<!--        </div>-->
        <?php 
    }
}

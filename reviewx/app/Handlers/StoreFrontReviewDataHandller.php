<?php

namespace ReviewX\Handlers;

use ReviewX\Api\WpApi;
use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Facades\View;
class StoreFrontReviewDataHandller implements InvokableContract
{
    protected WpApi $wpApi;
    /**
     *
     */
    public function __construct()
    {
        $this->wpApi = new WpApi();
    }
    public function __invoke()
    {
        View::output('storefront/reviews', ['title' => 'Welcome Deshboard review', 'content' => 'A WordPress Plugin development framework for humans', 'response' => $this->wpApi->get('/reviews')]);
    }
    public function storefrontTest($data)
    {
    }
}

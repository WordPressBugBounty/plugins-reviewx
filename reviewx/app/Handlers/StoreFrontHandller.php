<?php

namespace ReviewX\Handlers;

use ReviewX\Api\WpApi;
use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Facades\View;
class StoreFrontHandller implements InvokableContract
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
        View::output('storefront/widgets', ['title' => 'Welcome Deshboard', 'content' => 'A WordPress Plugin development framework for humans', 'response' => $this->wpApi->get('/reviews')]);
    }
}

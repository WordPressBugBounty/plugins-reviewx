<?php

namespace ReviewX\Handlers;

use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\Facades\View;
class ReviewReminderEmailHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('storeadmin/reviewReminder', ['title' => 'Welcome to WPDrill', 'content' => 'A WordPress Plugin development framework for humans']);
    }
}

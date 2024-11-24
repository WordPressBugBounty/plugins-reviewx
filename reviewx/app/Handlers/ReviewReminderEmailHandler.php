<?php

namespace Rvx\Handlers;

use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Facades\View;
class ReviewReminderEmailHandler implements InvokableContract
{
    public function __invoke()
    {
        View::output('reviewReminder', ['title' => 'Welcome to WPDrill', 'content' => 'A WordPress Plugin development framework for humans']);
    }
}

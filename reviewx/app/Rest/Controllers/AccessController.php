<?php

namespace Rvx\Rest\Controllers;

class AccessController
{
    public function adminAccess($request)
    {
        // do something
        //dd('Access Controller');
        $command = $request->get_params()['command'];
        //dd($command);
        if ($command === 'site_block') {
            $this->siteBlock();
        } elseif ($command === 'site_hit') {
            $this->siteHit();
        }
    }
    private function siteBlock()
    {
        // do something
        dd('Site Block Method');
    }
    private function siteHit()
    {
        // do something
        dd('Site Hit Method');
    }
}

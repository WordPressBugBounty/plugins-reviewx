<?php

namespace ReviewX\Rest\Controllers;

\defined("ABSPATH") || exit;
use ReviewX\Utilities\Helper;
use ReviewX\WPDrill\Contracts\InvokableContract;
class FileController implements InvokableContract
{
    public function __invoke()
    {
    }
    public function upload($request)
    {
        $file = $request->get_file('file');
        $response = $this->categoryService->processCategoryForSync($file);
        return Helper::saasResponse($response);
    }
}

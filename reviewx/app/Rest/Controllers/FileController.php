<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\CategoryService;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
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

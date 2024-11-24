<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
use Rvx\Utilities\Auth\Client;
class DataSyncApi extends \Rvx\Api\BaseApi
{
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function dataSync(array $files, $from) : Response
    {
        $fileName = $files['tmp_name'];
        return $this->withFile('file', $fileName, $files['full_path'])->post('sync?from=' . $from);
    }
}

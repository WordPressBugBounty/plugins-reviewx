<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
class DataSyncApi extends \Rvx\Api\BaseApi
{
    /**
     * @param array $files
     * @param string $from
     * @param int $total_objects
     * @return Response
     * @throws \Exception
     */
    public function dataSync(array $files, string $from = 'register', int $total_objects = 0) : Response
    {
        $fileName = $files['tmp_name'];
        return $this->withFile('file', $fileName, $files['full_path'])->post('sync?from=' . $from . '&total_objects=' . $total_objects);
    }
    public function syncStatus() : Response
    {
        return $this->get('/get/sync/status');
    }
}

<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
class DataSyncApi extends \Rvx\Api\BaseApi
{
    /**
     * @param array $files
     * @param string $from
     * @param int $object_count
     * @return Response
     * @throws \Exception
     */
    public function dataSync(array $files, string $from = 'register', int $object_count = 0) : Response
    {
        $fileName = $files['tmp_name'];
        return $this->withFile('file', $fileName, $files['full_path'])->post('sync?from=' . $from . '&total_lines=' . $object_count);
    }
    public function syncStatus() : Response
    {
        return $this->get('/get/sync/status');
    }
}

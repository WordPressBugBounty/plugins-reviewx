<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
use Exception;
use Rvx\Utilities\Auth\Client;
class ReviewImportAndExportApi extends \Rvx\Api\BaseApi
{
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function importSupportedAppStore(array $data) : Response
    {
        return $this->withJson($data)->post('admin/import/supported/app/store');
    }
    /**
     * @param array $data
     * @param array $files
     * @return Response
     * @throws Exception
     */
    public function importStore(array $data, array $files) : Response
    {
        $file = $files['file']['tmp_name'];
        $res = $this->withFormData($data)->withFile('file', $file, $files['file']['name'])->post('/reviews/import/store');
        return $res;
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function exportCsv(array $data) : Response
    {
        $new_product_ids = [];
        foreach ($data['product_ids'] as $product_id) {
            $new_product_ids[] = Client::getUid() . '-' . $product_id;
        }
        unset($data['product_ids']);
        $data['product_wp_unique_ids'] = $new_product_ids;
        return $this->withJson($data)->post('reviews/exports/generate/csv');
    }
    /**
     * @return Response
     * @throws Exception
     */
    public function exportHistory() : Response
    {
        return $this->get('reviews/exports/history');
    }
    /**
     * @return Response
     * @throws Exception
     */
    public function importHistory() : Response
    {
        return $this->get('reviews/import/history');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function importRollback(array $data) : Response
    {
        return $this->delete('reviews/import/rollback/' . $data['uid']);
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function importRestore(array $data) : Response
    {
        return $this->withJson($data)->put('admin/import/restore/7kAN-uGTkdlmo-4BAP');
    }
}

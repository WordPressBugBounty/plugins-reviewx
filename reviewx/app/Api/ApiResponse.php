<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
class ApiResponse extends Response
{
    /**
     * @return array
     */
    public function getApiData() : array
    {
        $response = $this->autoParse();
        return $response['data'] ?? [];
    }
    public function statusCode()
    {
        return $this->getStatusCode();
    }
}

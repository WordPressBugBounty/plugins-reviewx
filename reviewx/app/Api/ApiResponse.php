<?php

namespace ReviewX\Api;

use ReviewX\Apiz\Http\Response;
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

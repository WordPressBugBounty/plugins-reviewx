<?php

namespace ReviewX\Apiz\Http\Clients;

use ReviewX\GuzzleHttp\Client;
use ReviewX\GuzzleHttp\Psr7\Request;
use ReviewX\GuzzleHttp\Psr7\Response;
use ReviewX\GuzzleHttp\Psr7\Uri;
use ReviewX\GuzzleHttp\Exception\GuzzleException;
use ReviewX\Psr\Http\Message\RequestInterface;
use ReviewX\Psr\Http\Message\ResponseInterface;
class GuzzleClient extends AbstractClient
{
    /**
     * @inheritDoc
     * @return string
     */
    public function getRequestClass() : string
    {
        return Request::class;
    }
    /**
     * @inheritDoc
     */
    public function getResponseClass() : string
    {
        return Response::class;
    }
    /**
     * @inheritDoc
     */
    public function getUriClass() : string
    {
        return Uri::class;
    }
    /**
     * @param mixed ...$args
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function send(...$args) : ResponseInterface
    {
        $client = new Client($this->config);
        return $client->send(...$args);
    }
}

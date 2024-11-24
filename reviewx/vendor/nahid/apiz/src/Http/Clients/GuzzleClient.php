<?php

namespace Rvx\Apiz\Http\Clients;

use Rvx\GuzzleHttp\Client;
use Rvx\GuzzleHttp\Psr7\Request;
use Rvx\GuzzleHttp\Psr7\Response;
use Rvx\GuzzleHttp\Psr7\Uri;
use Rvx\GuzzleHttp\Exception\GuzzleException;
use Rvx\Psr\Http\Message\RequestInterface;
use Rvx\Psr\Http\Message\ResponseInterface;
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

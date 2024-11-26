<?php

declare (strict_types=1);
namespace Rvx\Nyholm\Psr7\Factory;

use Rvx\Http\Message\MessageFactory;
use Rvx\Http\Message\StreamFactory;
use Rvx\Http\Message\UriFactory;
use Rvx\Nyholm\Psr7\Request;
use Rvx\Nyholm\Psr7\Response;
use Rvx\Nyholm\Psr7\Stream;
use Rvx\Nyholm\Psr7\Uri;
use Rvx\Psr\Http\Message\RequestInterface;
use Rvx\Psr\Http\Message\ResponseInterface;
use Rvx\Psr\Http\Message\StreamInterface;
use Rvx\Psr\Http\Message\UriInterface;
if (!\interface_exists(MessageFactory::class)) {
    throw new \LogicException('You cannot use "Nyholm\\Psr7\\Factory\\HttplugFactory" as the "php-http/message-factory" package is not installed. Try running "composer require php-http/message-factory". Note that this package is deprecated, use "psr/http-factory" instead');
}
@\trigger_error('Class "Nyholm\\Psr7\\Factory\\HttplugFactory" is deprecated since version 1.8, use "Nyholm\\Psr7\\Factory\\Psr17Factory" instead.', \E_USER_DEPRECATED);
/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Martijn van der Ven <martijn@vanderven.se>
 *
 * @final This class should never be extended. See https://github.com/Nyholm/psr7/blob/master/doc/final.md
 *
 * @deprecated since version 1.8, use Psr17Factory instead
 */
class HttplugFactory implements MessageFactory, StreamFactory, UriFactory
{
    public function createRequest($method, $uri, array $headers = [], $body = null, $protocolVersion = '1.1') : RequestInterface
    {
        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }
    public function createResponse($statusCode = 200, $reasonPhrase = null, array $headers = [], $body = null, $version = '1.1') : ResponseInterface
    {
        return new Response((int) $statusCode, $headers, $body, $version, $reasonPhrase);
    }
    public function createStream($body = null) : StreamInterface
    {
        return Stream::create($body ?? '');
    }
    public function createUri($uri = '') : UriInterface
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        }
        return new Uri($uri);
    }
}
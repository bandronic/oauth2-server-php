<?php

namespace OAuth2Server\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use OAuth2Server\HttpFoundationBridge\Request;
use OAuth2Server\HttpFoundationBridge\Response;
use Psr\Http\Message\ServerRequestInterface;
use OAuth2\Server;
use Zend\Diactoros\Response\JsonResponse;

class Token implements MiddlewareInterface
{
    /** @var  Server */
    private $server;
    /** @var  Response */
    private $response;

    public function __construct(Server $server, Response $response)
    {
        $this->server = $server;
        $this->response = $response;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $request = Request::createFromPsrRequest($request);
        $this->server->handleTokenRequest($request, $this->response);

        $jsonResponse = new JsonResponse($this->response->getContent(), $this->response->getStatus(), $this->response->getHeaders()->all());

        return $jsonResponse;
    }
}
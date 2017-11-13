<?php

namespace OAuth2Server\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use OAuth2Server\HttpFoundationBridge\Request;
use OAuth2Server\HttpFoundationBridge\Response;
use OAuth2\Server;
use Zend\Diactoros\Response\JsonResponse;

class VerifyResource implements MiddlewareInterface
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
        $newRequest = Request::createFromPsrRequest($request);

        if (!$this->server->verifyResourceRequest($newRequest, $this->response)) {
            $response = $this->server->getResponse();

            $jsonResponse = new JsonResponse($response->getContent(), $response->getStatus(),
                $response->getHeaders()->all());

            if (empty($jsonResponse->getPayload()) || $jsonResponse->getPayload() == null) {
                $jsonResponse = $jsonResponse->withPayload(
                    [
                        'status' => $jsonResponse->getStatusCode(),
                        'title' => $jsonResponse->getReasonPhrase(),
                        'source' => $newRequest->getUri(),
                    ]);
            }

            return $jsonResponse;
        }

        return $delegate->process($request);
    }
}
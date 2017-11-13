<?php

namespace OAuth2Server\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use OAuth2Server\HttpFoundationBridge\Request;
use OAuth2Server\HttpFoundationBridge\Response;
use Psr\Http\Message\ServerRequestInterface;
use OAuth2\Server;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

class Authorize implements MiddlewareInterface
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

        if ($request->getMethod() == "GET") {
            if (!$this->server->validateAuthorizeRequest($newRequest, $this->response)) {
                $jsonResponse = new JsonResponse($this->response->getContent(), $this->response->getStatus(),
                    $this->response->getHeaders()->all());

                return $jsonResponse;
            }

            $path = $request->getRequestTarget();
            $responseType = $request->getQueryParams()['response_type'];
            $clientId = $request->getQueryParams()['client_id'];

            return new HtmlResponse($this->getAuthorizeForm($clientId, $responseType, $path));
        } else {
            if ($request->getMethod() == "POST") {
                $authorize = (bool)$request->getParsedBody()['authorize'];
                $this->server->handleAuthorizeRequest($newRequest, $this->response, $authorize);

                $jsonResponse = new JsonResponse($this->response->getContent(), $this->response->getStatus(),
                    $this->response->getHeaders()->all());

                return $jsonResponse;
            }
        }
    }

    private function getAuthorizeForm($client_id, $response_type, $path)
    {
        $form = "
        <h3>
            Welcome to the OAuth2.0 Server!
        </h3>
        <p>
            You have been sent here by <strong>$client_id</strong>.  $client_id would like to access the following data:
        </p>
        <ul>
            <li>friends</li>
            <li>memories</li>
            <li>hopes, dreams, passions, etc.</li>
            <li>sock drawer</li>
        </ul>
        <p>It will use this data to:</p>
        <ul>
            <li>integrate with friends</li>
            <li>make your life better</li>
            <li>miscellaneous nefarious purposes</li>
        </ul>
        <p>Click the button below to complete the authorize request and grant an <code>" . ($response_type == 'code' ? 'Authorization Code' : 'Access Token') . "}}</code> to $client_id.
        <ul class=\"authorize_options\">
            <li>
                <form action=\"$path\" method=\"post\">
                    <input type=\"submit\" class=\"button authorize\" value=\"Yes, I Authorize This Request\" />
                    <input type=\"hidden\" name=\"authorize\" value=\"1\" />
                </form>
            </li>
            <li class=\"cancel\">
                <form id=\"cancel\" action=\"$path\" method=\"post\">
                    <a href=\"#\" onclick=\"document.getElementById('cancel').submit()\">cancel</a>
                    <input type=\"hidden\" name=\"authorize\" value=\"0\" />
                </form>
            </li>
        </ul>";
        return $form;
    }
}
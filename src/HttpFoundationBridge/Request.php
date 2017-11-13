<?php

namespace OAuth2Server\HttpFoundationBridge;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use OAuth2\RequestInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class Request extends BaseRequest implements RequestInterface
{
    public function query($name, $default = null)
    {
        return $this->query->get($name, $default);
    }

    public function request($name, $default = null)
    {
        return $this->request->get($name, $default);
    }

    public function server($name, $default = null)
    {
        return $this->server->get($name, $default);
    }

    public function headers($name, $default = null)
    {
        return $this->headers->get($name, $default);
    }

    public function getAllQueryParameters()
    {
        return $this->query->all();
    }

    public static function createFromPsrRequest(ServerRequestInterface $request)
    {
        /**
         * @param array $query The GET parameters
         * @param array $request The POST parameters
         * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
         * @param array $cookies The COOKIE parameters
         * @param array $files The FILES parameters
         * @param array $server The SERVER parameters
         * @param string|resource $content The raw body data
         */

        return new static($request->getQueryParams(), $request->getParsedBody(), $request->getAttributes(),
            $request->getCookieParams(), $request->getUploadedFiles(), $request->getServerParams(),
            $request->getBody());
    }

    public static function createFromRequest(BaseRequest $request)
    {
        return new static($request->query->all(), $request->request->all(), $request->attributes->all(),
            $request->cookies->all(), $request->files->all(), $request->server->all(), $request->getContent());
    }

    public static function createFromRequestStack(RequestStack $request)
    {
        $request = $request->getCurrentRequest();
        return self::createFromRequest($request);
    }

    /**
     * Creates a new request with values from PHP's super globals.
     * Overwrite to fix an apache header bug. Read more here:
     * http://stackoverflow.com/questions/11990388/request-headers-bag-is-missing-authorization-header-in-symfony-2%E2%80%94
     *
     * @return Request A new request
     *
     * @api
     */
    public static function createFromGlobals()
    {
        $request = parent::createFromGlobals();

        //fix the bug.
        self::fixAuthHeader($request->headers);

        return $request;
    }

    /**
     * PHP does not include HTTP_AUTHORIZATION in the $_SERVER array, so this header is missing.
     * We retrieve it from apache_request_headers()
     *
     * @see https://github.com/symfony/symfony/issues/7170
     *
     * @param HeaderBag $headers
     */
    protected static function fixAuthHeader(HeaderBag $headers)
    {
        if (!$headers->has('Authorization') && function_exists('apache_request_headers')) {
            $all = apache_request_headers();
            if (isset($all['Authorization'])) {
                $headers->set('Authorization', $all['Authorization']);
            }
        }
    }
}

<?php

namespace OAuth2Server\HttpFoundationBridge;

use OAuth2\ResponseInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 *
 */
class Response implements ResponseInterface
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var \Symfony\Component\HttpFoundation\ResponseHeaderBag
     */
    public $headers;

    protected $data;
    protected $callback;

    // Encode <, >, ', &, and " characters in the JSON, making it also safe to be embedded into HTML.
    // 15 === JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    const DEFAULT_ENCODING_OPTIONS = 15;

    protected $encodingOptions = self::DEFAULT_ENCODING_OPTIONS;

    /**
     * @param mixed $content The response content, see setContent()
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->headers = new ResponseHeaderBag($headers);
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
    }

    public function addParameters(array $parameters)
    {
        // if there are existing parametes, add to them
        if ($this->content && $data = json_decode($this->content, true)) {
            $parameters = array_merge($data, $parameters);
        }

        // this will encode the php array as json data
        $this->setData($parameters);
    }

    public function addHttpHeaders(array $httpHeaders)
    {
        foreach ($httpHeaders as $key => $value) {
            $this->headers->set($key, $value);
        }
    }

    public function getParameter($name)
    {
        if ($this->content && $data = json_decode($this->content, true)) {
            return isset($data[$name]) ? $data[$name] : null;
        }
    }

    public function setError($statusCode, $error, $description = null, $uri = null)
    {
        $this->setStatusCode($statusCode);
        $this->addParameters(array_filter(array(
            'error' => $error,
            'error_description' => $description,
            'error_uri' => $uri,
        )));
    }

    public function setRedirect(
        $statusCode = 302,
        $url,
        $state = null,
        $error = null,
        $errorDescription = null,
        $errorUri = null
    ) {
        $this->setStatusCode($statusCode);

        $params = array_filter(array(
            'state' => $state,
            'error' => $error,
            'error_description' => $errorDescription,
            'error_uri' => $errorUri,
        ));

        if ($params) {
            // add the params to the URL
            $parts = parse_url($url);
            $sep = isset($parts['query']) && count($parts['query']) > 0 ? '&' : '?';
            $url .= $sep . http_build_query($params);
        }

        $this->headers->set('Location', $url);
    }

    /**
     * Sets the response status code.
     *
     * If the status text is null it will be automatically populated for the known
     * status codes and left empty otherwise.
     *
     * @return $this
     * @param $code
     *
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     *
     * @final since version 3.2
     */
    public function setStatusCode($code)
    {
        $this->statusCode = $code;
        if ($this->isInvalid()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }

        $this->statusText = '';

        return $this;
    }

    /**
     * Sets the data to be sent as JSON.
     *
     * @param mixed $data
     *
     * @return $this
     *
     * @throws
     */
    public function setData($data = array())
    {
        if (defined('HHVM_VERSION')) {
            // HHVM does not trigger any warnings and let exceptions
            // thrown from a JsonSerializable object pass through.
            // If only PHP did the same...
            $data = json_encode($data, $this->encodingOptions);
        } else {
            if (!interface_exists('JsonSerializable', false)) {
                set_error_handler(function () { return false; });
                try {
                    $data = @json_encode($data, $this->encodingOptions);
                } finally {
                    restore_error_handler();
                }
            } else {
                try {
                    $data = json_encode($data, $this->encodingOptions);
                } catch (\Exception $e) {
                    if ('Exception' === get_class($e) && 0 === strpos($e->getMessage(), 'Failed calling ')) {
                        throw $e->getPrevious() ?: $e;
                    }
                    throw $e;
                }
            }
        }

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        return $this->setJson($data);
    }

    /**
     * Sets a raw string containing a JSON document to be sent.
     *
     * @param string $json
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setJson($json)
    {
        $this->data = $json;

        return $this->update();
    }

    /**
     * Updates the content and headers according to the JSON data and callback.
     *
     * @return $this
     */
    protected function update()
    {
        if (null !== $this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');

            return $this->setContent(sprintf('/**/%s(%s);', $this->callback, $this->data));
        }

        // Only set the header when there is none or when it equals 'text/javascript' (from a previous update with callback)
        // in order to not overwrite a custom definition.
        if (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }

        return $this->setContent($this->data);
    }

    /**
     * Sets the response content.
     *
     * Valid types are strings, numbers, null, and objects that implement a __toString() method.
     *
     * @param mixed $content Content that can be cast to string
     *
     * @return $this
     *
     * @throws \UnexpectedValueException
     */
    public function setContent($content)
    {
        if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable(array($content, '__toString'))) {
            throw new \UnexpectedValueException(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.', gettype($content)));
        }

        $this->content = (string) $content;

        return $this;
    }

    public function isInvalid()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * Sets the HTTP protocol version (1.0 or 1.1).
     *
     * @param string $version The HTTP protocol version
     *
     * @return $this
     *
     * @final since version 3.2
     */
    public function setProtocolVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    public function getContent() {
        return json_decode($this->content);
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getStatus() {
        return $this->statusCode;
    }
}

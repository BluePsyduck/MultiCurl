<?php

/**
 * The request entity.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */

namespace BluePsyduck\MultiCurl\Entity;

class Request {
    /**
     * The request uses the GET method.
     */
    const METHOD_GET = 'get';

    /**
     * The request uses the POST method.
     */
    const METHOD_POST = 'post';

    /**
     * The method to use for the request.
     * @var string
     */
    protected $method = self::METHOD_GET;

    /**
     * The URL to request.
     * @var string
     */
    protected $url = '';

    /**
     * The request data to send with the request.
     * @var string
     */
    protected $requestData;

    /**
     * Additional headers to send with the request.
     * @var array
     */
    protected $headers = array();

    /**
     * The timeout to use.
     * @var int
     */
    protected $timeout = 0;

    /**
     * The username to use for basic authentication.
     * @var string
     */
    protected $basicAuthUsername = '';

    /**
     * The password to use for basic authentication.
     * @var string
     */
    protected $basicAuthPassword = '';

    /**
     * Sets the method to use for the request.
     * @param string $method
     * @return $this Implementing fluent interface.
     */
    public function setMethod($method) {
        $this->method = $method;
        return $this;
    }

    /**
     * Returns the method to use for the request.
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Sets the URL to request.
     * @param string $url
     * @return $this Implementing fluent interface.
     */
    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    /**
     * Returns the URL to request.
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Sets the request data to send with the request.
     * @param string|array $requestData
     * @return $this Implementing fluent interface.
     */
    public function setRequestData($requestData) {
        if (is_array($requestData)) {
            $this->requestData = http_build_query($requestData);
        } else {
            $this->requestData = $requestData;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestData() {
        return $this->requestData;
    }

    /**
     * Sets the additional headers to send with the request.
     * @param array $headers
     * @return $this Implementing fluent interface.
     */
    public function setHeaders(array $headers) {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Returns the additional headers to send with the request.
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Sets the timeout to use.
     * @param int $timeout
     * @return $this Implementing fluent interface.
     */
    public function setTimeout($timeout) {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Returns the timeout to use.
     * @return int
     */
    public function getTimeout() {
        return $this->timeout;
    }

    /**
     * Sets the credentials for the basic authentication.
     * @param string $username The username to use.
     * @param string $password The password to use.
     * @return $this Implementing fluent interface.
     */
    public function setBasicAuth($username, $password) {
        $this->basicAuthUsername = $username;
        $this->basicAuthPassword = $password;
        return $this;
    }

    /**
     * Returns the username to use for basic authentication.
     * @return string
     */
    public function getBasicAuthUsername() {
        return $this->basicAuthUsername;
    }

    /**
     * Returns the password to use for basic authentication.
     * @return string
     */
    public function getBasicAuthPassword() {
        return $this->basicAuthPassword;
    }
}
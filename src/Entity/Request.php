<?php

namespace BluePsyduck\MultiCurl\Entity;

use BluePsyduck\MultiCurl\Constant\RequestMethod;
use BluePsyduck\MultiCurl\Wrapper\Curl;

/**
 * The entity representing a request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */
class Request
{
    /**
     * The method to use for the request.
     * @var string
     */
    protected $method = RequestMethod::GET;

    /**
     * The URL to request.
     * @var string
     */
    protected $url = '';

    /**
     * The request data to send with the request.
     * @var string
     */
    protected $requestData = '';

    /**
     * The additional header to send with the request.
     * @var Header
     */
    protected $header;

    /**
     * The timeout to use, in seconds.
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
     * The callback to execute on initializing the cUrl request.
     * @var callable|null
     */
    protected $onInitializeCallback = null;

    /**
     * The callback to execute on completing the request.
     * @var callable|null
     */
    protected $onCompleteCallback = null;

    /**
     * The cUrl instance used for executing the request.
     * @var Curl
     */
    protected $curl;

    /**
     * The response entity, available once the request has been completed.
     * @var Response
     */
    protected $response;

    /**
     * Initializes the request.
     */
    public function __construct()
    {
        $this->header = new Header();
        $this->curl = new Curl();
        $this->response = new Response();
    }

    /**
     * Clones the request.
     */
    public function __clone()
    {
        $this->header = clone($this->header);
        $this->curl = new Curl();
        $this->response = clone($this->response);
    }

    /**
     * Sets the method to use for the request.
     * @param string $method
     * @return $this Implementing fluent interface.
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Returns the method to use for the request.
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Sets the URL to request.
     * @param string $url
     * @return $this Implementing fluent interface.
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Returns the URL to request.
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Sets the request data to send with the request.
     * @param string|array $requestData
     * @return $this Implementing fluent interface.
     */
    public function setRequestData($requestData)
    {
        if (is_array($requestData)) {
            $this->requestData = http_build_query($requestData);
        } else {
            $this->requestData = $requestData;
        }
        return $this;
    }

    /**
     * Returns the request data to send with the request.
     * @return string
     */
    public function getRequestData(): string
    {
        return $this->requestData;
    }

    /**
     * Returns additional the header to send with the request.
     * @return Header
     */
    public function getHeader(): Header
    {
        return $this->header;
    }

    /**
     * Sets the timeout to use, in seconds.
     * @param int $timeout
     * @return $this Implementing fluent interface.
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Returns the timeout to use, in seconds.
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Sets the credentials for the basic authentication.
     * @param string $username The username to use.
     * @param string $password The password to use.
     * @return $this Implementing fluent interface.
     */
    public function setBasicAuth(string $username, string $password)
    {
        $this->basicAuthUsername = $username;
        $this->basicAuthPassword = $password;
        return $this;
    }

    /**
     * Returns the username to use for basic authentication.
     * @return string
     */
    public function getBasicAuthUsername(): string
    {
        return $this->basicAuthUsername;
    }

    /**
     * Returns the password to use for basic authentication.
     * @return string
     */
    public function getBasicAuthPassword(): string
    {
        return $this->basicAuthPassword;
    }

    /**
     * Sets the callback to execute on initializing the cUrl request.
     * @param callable $onInitializeCallback The callback must expect exactly one parameter: The request entity.
     * @return $this Implementing fluent interface.
     */
    public function setOnInitializeCallback(callable $onInitializeCallback)
    {
        $this->onInitializeCallback = $onInitializeCallback;
        return $this;
    }

    /**
     * Returns the callback to execute on initializing the cUrl request.
     * @return callable|null
     */
    public function getOnInitializeCallback()
    {
        return $this->onInitializeCallback;
    }

    /**
     * Sets the callback to execute on completing the request.
     * @param callable $onCompleteCallback The callback must expect exactly one parameter: The request entity.
     * @return $this Implementing fluent interface.
     */
    public function setOnCompleteCallback(callable $onCompleteCallback)
    {
        $this->onCompleteCallback = $onCompleteCallback;
        return $this;
    }

    /**
     * Returns the callback to execute on completing the request.
     * @return callable|null
     */
    public function getOnCompleteCallback()
    {
        return $this->onCompleteCallback;
    }

    /**
     * Returns the cUrl instance used for executing the request.
     * @return Curl
     */
    public function getCurl(): Curl
    {
        return $this->curl;
    }

    /**
     * Returns the response entity, available once the request has been completed.
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
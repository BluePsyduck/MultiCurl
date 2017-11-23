<?php

namespace BluePsyduck\MultiCurl\Entity;

/**
 * The entity representing a response to a request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */
class Response
{
    /**
     * The cUrl error code in case an error occurred while executing the request.
     * @var int
     */
    protected $errorCode = CURLE_OK;

    /**
     * The cUrl message code in case an error occurred while executing the request.
     * @var string
     */
    protected $errorMessage = '';

    /**
     * The status code of the response.
     * @var int
     */
    protected $statusCode = 0;

    /**
     * The headers of the response.
     * @var array|Header[]
     */
    protected $headers = [];

    /**
     * The content of the response.
     * @var string
     */
    protected $content = '';

    /**
     * Clones the response.
     */
    public function __clone()
    {
        $this->headers = array_map(function(Header $header): Header {
            return clone($header);
        }, $this->headers);
    }

    /**
     * Sets the cUrl error code in case an error occurred while executing the request.
     * @param int $errorCode
     * @return $this Implementing fluent interface.
     */
    public function setErrorCode(int $errorCode)
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * Returns the cUrl error code in case an error occurred while executing the request.
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * Sets the cUrl error message in case an error occurred while executing the request.
     * @param string $errorMessage
     * @return $this Implementing fluent interface.
     */
    public function setErrorMessage(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * Returns the cUrl error message in case an error occurred while executing the request.
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param int $statusCode
     * @return $this Implementing fluent interface.
     */
    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Returns the status code of the response.
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Adds a header to the response.
     * @param Header $header
     * @return $this Implementing fluent interface.
     */
    public function addHeader(Header $header)
    {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * Returns the headers of the response.
     * @return array|Header[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns the header of the last redirect of the request.
     * @return Header|null
     */
    public function getLastHeader()
    {
        return reset($this->headers);
    }

    /**
     * Sets the content of the response.
     * @param string $content
     * @return $this Implementing fluent interface.
     */
    public function setContent(string $content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Returns the content of the response.
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
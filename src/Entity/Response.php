<?php

/**
 * The response entity.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */

namespace BluePsyduck\MultiCurl\Entity;

class Response {
    /**
     * The status code of the response.
     * @var int
     */
    protected $statusCode;

    /**
     * The headers of the response.
     * @var array
     */
    protected $headers = array();

    /**
     * The content of the response.
     * @var string
     */
    protected $content = '';

    /**
     * @param int $statusCode
     * @return $this Implementing fluent interface.
     */
    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Returns the status code of the response.
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Sets the headers of the response.
     * @param array $headers
     * @return $this Implementing fluent interface.
     */
    public function setHeaders(array $headers) {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Returns the headers of the response.
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Sets the content of the response.
     * @param string $content
     * @return $this Implementing fluent interface.
     */
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    /**
     * Returns the content of the response.
     * @return string
     */
    public function getContent() {
        return $this->content;
    }
}
<?php

/**
 * Wrapper class for the curl_* functions.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */

namespace BluePsyduck\MultiCurl\Wrapper;

class Curl {
    /**
     * The cURL resource.
     * @var resource
     */
    protected $handle;

    /**
     * Initializes the cUrl.
     */
    public function __construct() {
        $this->handle = curl_init();
    }

    /**
     * Finalizes the cUrl.
     */
    public function __destruct() {
        curl_close($this->handle);
    }

    /**
     * Returns the handle of the cURL instance.
     * @return resource
     */
    public function getHandle() {
        return $this->handle;
    }

    /**
     * Sets an option for the cURL instance.
     * @param string $name The cURL internal name of the option.
     * @param mixed $value The value to set.
     * @return $this Implementing fluent interface,
     */
    public function setOption($name, $value) {
        curl_setopt($this->handle, $name, $value);
        return $this;
    }

    /**
     * Executes the cURL instance.
     * @return mixed The response.
     */
    public function execute() {
        return curl_exec($this->handle);
    }

    /**
     * Returns the information to the cURL instance.
     * @param string $name The name of the info to return.
     * @return mixed The information.
     */
    public function getInfo($name) {
        return curl_getinfo($this->handle, $name);
    }

    /**
     * Returns the error code of the cURL instance.
     * @return int
     */
    public function getErrorCode() {
        return curl_errno($this->handle);
    }

    /**
     * Returns the error message of the cURL instance.
     * @return string
     */
    public function getErrorMessage() {
        return curl_error($this->handle);
    }
}
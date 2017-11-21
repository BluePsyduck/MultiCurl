<?php

namespace BluePsyduck\MultiCurl\Wrapper;

/**
 * Wrapper class for the curl_* functions.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */
class Curl
{
    /**
     * The cURL resource.
     * @var resource
     */
    protected $handle;

    /**
     * Initializes the cUrl.
     */
    public function __construct()
    {
        $this->handle = curl_init();
    }

    /**
     * Finalizes the cUrl.
     */
    public function __destruct()
    {
        curl_close($this->handle);
    }

    /**
     * Returns the handle of the cURL instance.
     * @return resource
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Sets an option for the cURL instance.
     * @param string $name The cURL internal name of the option.
     * @param mixed $value The value to set.
     * @return $this Implementing fluent interface,
     */
    public function setOption(string $name, $value)
    {
        curl_setopt($this->handle, $name, $value);
        return $this;
    }

    /**
     * Executes the cURL instance.
     * @return mixed The response.
     */
    public function execute()
    {
        return curl_exec($this->handle);
    }

    /**
     * Returns the information to the cURL instance.
     * @param int|null $code The code of the info to return.
     * @return mixed The information.
     */
    public function getInfo($code = null)
    {
        return curl_getinfo($this->handle, $code);
    }

    /**
     * Returns the error code of the cURL instance.
     * @return int
     */
    public function getErrorCode(): int
    {
        return curl_errno($this->handle);
    }

    /**
     * Returns the error message of the cURL instance.
     * @return string
     */
    public function getErrorMessage(): string
    {
        return curl_error($this->handle);
    }
}
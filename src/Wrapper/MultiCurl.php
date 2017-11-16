<?php

namespace BluePsyduck\MultiCurl\Wrapper;

/**
 * Wrapper class for the curl_multi_* functions.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */
class MultiCurl
{
    /**
     * The handle of the multi cUrl.
     * @var resource
     */
    protected $handle;

    /**
     * The current execution code of the multi cUrl.
     * @var int
     */
    protected $currentExecutionCode = CURLM_OK;

    /**
     * The number of requests which as still running.
     * @var int
     */
    protected $stillRunningRequests = 0;

    /**
     * Initializes the multi cUrl.
     */
    public function __construct()
    {
        $this->handle = curl_multi_init();
    }

    /**
     * Finalizes the multi cUrl.
     */
    public function __destruct()
    {
        curl_multi_close($this->handle);
    }

    /**
     * Adds a cUrl instance to the multi cUrl.
     * @param \BluePsyduck\MultiCurl\Wrapper\Curl $curl
     * @return $this Implementing fluent interface.
     */
    public function addCurl(Curl $curl)
    {
        curl_multi_add_handle($this->handle, $curl->getHandle());
        return $this;
    }

    /**
     * Removes the specified cUrl instance from the multi cUrl.
     * @param \BluePsyduck\MultiCurl\Wrapper\Curl $curl
     * @return $this Implementing fluent interface.
     */
    public function removeCurl(Curl $curl)
    {
        curl_multi_remove_handle($this->handle, $curl->getHandle());
        return $this;
    }

    /**
     * Returns the content of the specified cUrl instance.
     * @param \BluePsyduck\MultiCurl\Wrapper\Curl $curl
     * @return string The content.
     */
    public function getContent(Curl $curl): string
    {
        return curl_multi_getcontent($curl->getHandle());
    }

    /**
     * Executes the multi cUrl request.
     * @return $this Implementing fluent interface.
     */
    public function execute()
    {
        $this->currentExecutionCode = curl_multi_exec($this->handle, $this->stillRunningRequests);
        return $this;
    }

    /**
     * Reads the next status message from the cUrl requests.
     * @return array|false Either the status message as array, or false if there are no mor messages.
     */
    public function readInfo()
    {
        return curl_multi_info_read($this->handle);
    }

    /**
     * Selects all sockets which have an activity.
     * @param int|null $timeout The timeout in seconds to wait.
     * @return int The number of selected descriptors.
     */
    public function select(int $timeout = null): int
    {
        return curl_multi_select($this->handle, $timeout);
    }

    /**
     * Returns the current execution code of the multi cUrl.
     * @return int
     */
    public function getCurrentExecutionCode(): int
    {
        return $this->currentExecutionCode;
    }

    /**
     * Returns the number of requests which as still running.
     * @return int
     */
    public function getStillRunningRequests(): int
    {
        return $this->stillRunningRequests;
    }
}
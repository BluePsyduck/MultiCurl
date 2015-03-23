<?php
/**
 * Wrapper class for the curl_multi_* functions.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */

namespace BluePsyduck\MultiCurl\Wrapper;

class MultiCurl {
    /**
     * The handle of the multi cUrl.
     * @var resource
     */
    protected $handle;

    /**
     * The current execution code of the multi cUrl.
     * @var int
     */
    protected $currentExecutionCode;

    /**
     * The number of requests which as still running.
     * @var int
     */
    protected $stillRunningRequests;

    /**
     * Initializes the multi cUrl.
     */
    public function __construct() {
        $this->handle = curl_multi_init();
    }

    /**
     * Finalizes the multi cUrl.
     */
    public function __destruct() {
        curl_multi_close($this->handle);
    }

    /**
     * Adds a cUrl instance to the multi cUrl.
     * @param \BluePsyduck\MultiCurl\Wrapper\Curl $curl
     * @return $this Implementing fluent interface.
     */
    public function addCurl(Curl $curl) {
        curl_multi_add_handle($this->handle, $curl->getHandle());
        return $this;
    }

    /**
     * Removes the specified cUrl instance from the multi cUrl.
     * @param \BluePsyduck\MultiCurl\Wrapper\Curl $curl
     * @return $this Implementing fluent interface.
     */
    public function removeCurl(Curl $curl) {
        curl_multi_remove_handle($this->handle, $curl->getHandle());
        return $this;
    }

    /**
     * Returns the content of the specified cUrl instance.
     * @param \BluePsyduck\MultiCurl\Wrapper\Curl $curl
     * @return string The content.
     */
    public function getContent(Curl $curl) {
        return curl_multi_getcontent($curl->getHandle());
    }

    /**
     * Executes the multi cUrl request.
     * @return $this Implementing fluent interface.
     */
    public function execute() {
        $this->currentExecutionCode = curl_multi_exec($this->handle, $this->stillRunningRequests);
        return $this;
    }

    /**
     * Selects all sockets which have an activity.
     * @param int|null $timeout The timeout in seconds to wait.
     * @return int The number of selected descriptors.
     */
    public function select($timeout = null) {
        return curl_multi_select($this->handle, $timeout);
    }

    /**
     * Returns the current execution code of the multi cUrl.
     * @return int
     */
    public function getCurrentExecutionCode() {
        return $this->currentExecutionCode;
    }

    /**
     * Returns the number of requests which as still running.
     * @return int
     */
    public function getStillRunningRequests() {
        return $this->stillRunningRequests;
    }
}
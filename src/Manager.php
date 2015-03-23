<?php
/**
 * The main manager of the Multi-cURL.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */

namespace BluePsyduck\MultiCurl;

use BluePsyduck\MultiCurl\Entity\Request;
use BluePsyduck\MultiCurl\Entity\Response;
use BluePsyduck\MultiCurl\Wrapper\Curl;
use BluePsyduck\MultiCurl\Wrapper\MultiCurl;

class Manager {
    /**
     * Thr requests to execute.
     * @var array|\BluePsyduck\MultiCurl\Entity\Request[]
     */
    protected $requests = array();

    /**
     * The cUrl instances.
     * @var array|\BluePsyduck\MultiCurl\Wrapper\Curl[]
     */
    protected $curls = array();

    /**
     * The responses.
     * @var array|\BluePsyduck\MultiCurl\Entity\Response[]
     */
    protected $responses = array();

    /**
     * The multi cUrl instance.
     * @var \BluePsyduck\MultiCurl\Wrapper\MultiCurl
     */
    protected $multiCurl;

    /**
     * Adds a request to the MultiCurl manager.
     * @param string $name A name to identify the request.
     * @param \BluePsyduck\MultiCurl\Entity\Request $request
     * @return $this Implementing fluent interface.
     */
    public function addRequest($name, Request $request) {
        $this->requests[$name] = $request;
        return $this;
    }

    /**
     * Executes all the requests. This method may return before all requests have been finished.
     * @return $this Implementing fluent interface.
     */
    public function execute() {
        $this->multiCurl = new MultiCurl();
        $this->curls = $this->initializeMultiCurl($this->requests, $this->multiCurl);
        $this->start();
        return $this;
    }

    /**
     * Initializes the MultiCurl request.
     * @param array|\BluePsyduck\MultiCurl\Entity\Request[] $requests
     * @param \BluePsyduck\MultiCurl\Wrapper\MultiCurl $multiCurl
     * @return array|\BluePsyduck\MultiCurl\Wrapper\Curl[] The curl instances created during initialization.
     */
    protected function initializeMultiCurl(array $requests, MultiCurl $multiCurl) {
        $result = array();
        foreach ($requests as $name => $request) {
            $curl = new Curl();
            $this->initializeCurlForRequest($request, $curl);
            $result[$name] = $curl;
            $multiCurl->addCurl($curl);
        }
        return $result;
    }

    /**
     * Initializes the CURL for the specified request.
     * @param \BluePsyduck\MultiCurl\Entity\Request $request
     * @param \BluePsyduck\MultiCurl\Wrapper\Curl $curl
     * @return $this Implementing fluent interface.
     */
    protected function initializeCurlForRequest(Request $request, Curl $curl) {
        if ($request->getMethod() === Request::METHOD_POST) {
            $curl->setOption(CURLOPT_POST, true)
                 ->setOption(CURLOPT_POSTFIELDS, $request->getRequestData());
        } else {
            $curl->setOption(CURLOPT_HTTPGET, true);
        }

        $curl->setOption(CURLOPT_URL, $request->getUrl())
             ->setOption(CURLOPT_RETURNTRANSFER, true)
             ->setOption(CURLOPT_HEADER, true)
             ->setOption(CURLOPT_FOLLOWLOCATION, true);

        if ($request->getTimeout() > 0) {
            $curl->setOption(CURLOPT_TIMEOUT, $request->getTimeout());
        }
        if ($request->getHeaders()) {
            $curl->setOption(CURLOPT_HTTPHEADER, $request->getHeaders());
        }
        return $this;
    }

    /**
     * Starts executing the requests.
     * @return $this Implementing fluent interface.
     */
    protected function start() {
        do {
            $this->multiCurl->execute();
        } while ($this->multiCurl->getCurrentExecutionCode() === CURLM_CALL_MULTI_PERFORM);
        return $this;
    }


    /**
     * Delays the script execution until all requests have been finished.
     * @return $this Implementing fluent interface.
     */
    public function waitForRequests() {
        while ($this->multiCurl->getStillRunningRequests() > 0
            && $this->multiCurl->getCurrentExecutionCode() === CURLM_OK
        ) {
            if ($this->multiCurl->select() != -1) {
                $this->start();
            }
        }
        return $this;
    }

    /**
     * Returns the response of the specified request.
     * @param string $name The name of the request.
     * @return \BluePsyduck\MultiCurl\Entity\Response|null
     */
    public function getResponse($name) {
        $response = null;
        if (array_key_exists($name, $this->responses)) {
            $response = $this->responses[$name];
        } elseif (array_key_exists($name, $this->requests)) {
            $response = $this->parseResponse($name);
            $this->responses[$name] = $response;
        }
        return $response;
    }

    /**
     * Parses the response of the request with the specified name.
     * @param string $name
     * @return \BluePsyduck\MultiCurl\Entity\Response|null
     */
    protected function parseResponse($name) {
        $result = null;
        if (array_key_exists($name, $this->curls)) {
            $curl = $this->curls[$name];

            $headerSize = $curl->getInfo(CURLINFO_HEADER_SIZE);
            $rawContent = $this->multiCurl->getContent($curl);

            $result = new Response();
            $result->setStatusCode($curl->getInfo(CURLINFO_HTTP_CODE))
                   ->setHeaders($this->parseHeaders(substr($rawContent, 0, $headerSize)))
                   ->setContent(substr($rawContent, $headerSize));

            $this->multiCurl->removeCurl($curl);
            unset($this->curls[$name]);
        }
        return $result;
    }

    /**
     * Parses the header string into an associative array.
     * @param string $headerString
     * @return array
     */
    protected function parseHeaders($headerString) {
        $result = array();
        foreach (explode("\r\n", $headerString) as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $result[trim($parts[0])] = trim($parts[1]);
            }
        }
        return $result;
    }
}
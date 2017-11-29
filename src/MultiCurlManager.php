<?php

namespace BluePsyduck\MultiCurl;

use BluePsyduck\MultiCurl\Entity\Header;
use BluePsyduck\MultiCurl\Entity\Request;
use BluePsyduck\MultiCurl\Entity\Response;
use BluePsyduck\MultiCurl\Wrapper\Curl;
use BluePsyduck\MultiCurl\Wrapper\MultiCurl;

/**
 * The main class of the multi cUrl library.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */
class MultiCurlManager
{
    /**
     * The multi cUrl instance.
     * @var MultiCurl
     */
    protected $multiCurl;

    /**
     * The number of requests to execute in parallel, or 0 if not limited.
     * @var int
     */
    protected $numberOfParallelRequests = 0;

    /**
     * The requests waiting to be executed.
     * @var array|Request[]
     */
    protected $waitingRequests = [];

    /**
     * The requests currently being executed.
     * @var array|Request[]
     */
    protected $runningRequests = [];

    /**
     * Initializes the manager.
     */
    public function __construct()
    {
        $this->multiCurl = new MultiCurl();
    }

    /**
     * Sets the number of requests to execute in parallel, or 0 if not limited.
     * @param int $numberOfParallelRequests
     * @return $this Implementing fluent interface.
     */
    public function setNumberOfParallelRequests(int $numberOfParallelRequests)
    {
        $this->numberOfParallelRequests = $numberOfParallelRequests;
        return $this;
    }

    /**
     * Adds a request to the MultiCurl manager.
     * @param \BluePsyduck\MultiCurl\Entity\Request $request
     * @return $this Implementing fluent interface.
     */
    public function addRequest(Request $request)
    {
        $this->waitingRequests[] = $request;
        $this->executeNextWaitingRequest();
        return $this;
    }

    /**
     * Executes the next waiting request, if there is still one to be executed.
     * @return $this
     */
    protected function executeNextWaitingRequest()
    {
        if (($this->numberOfParallelRequests === 0 || count($this->runningRequests) < $this->numberOfParallelRequests)
            && count($this->waitingRequests) > 0
        ) {
            $this->executeRequest(reset($this->waitingRequests));
        }
        return $this;
    }

    /**
     * Executes the specified request, if it is still waiting.
     * @param Request $request
     * @return $this
     */
    protected function executeRequest(Request $request)
    {
        $index = array_search($request, $this->waitingRequests);
        if ($index !== false) {
            unset($this->waitingRequests[$index]);
            $this->hydrateCurlFromRequest($request, $request->getCurl())
                 ->triggerCallback($request->getOnInitializeCallback(), $request);

            $this->multiCurl->addCurl($request->getCurl());
            $this->runningRequests[] = $request;
            $this->executeMultiCurl();
        }
        return $this;
    }

    /**
     * Hydrates the cURL instance with the data from the specified request.
     * @param Request $request
     * @param Curl $curl
     * @return $this Implementing fluent interface.
     */
    protected function hydrateCurlFromRequest(Request $request, Curl $curl)
    {
        $curl->setOption(CURLOPT_CUSTOMREQUEST, $request->getMethod())
             ->setOption(CURLOPT_URL, $request->getUrl())
             ->setOption(CURLOPT_RETURNTRANSFER, true)
             ->setOption(CURLOPT_HEADER, true)
             ->setOption(CURLOPT_FOLLOWLOCATION, true)
             ->setOption(CURLOPT_HTTPHEADER, $this->prepareRequestHeader($request->getHeader()));

        if (strlen($request->getRequestData()) > 0) {
            $curl->setOption(CURLOPT_POSTFIELDS, $request->getRequestData());
        }
        if ($request->getTimeout() > 0) {
            $curl->setOption(CURLOPT_TIMEOUT, $request->getTimeout());
        }
        if ($request->getBasicAuthUsername() && $request->getBasicAuthPassword()) {
            $credentials = $request->getBasicAuthUsername() . ':' . $request->getBasicAuthPassword();
            $curl->setOption(CURLOPT_USERPWD, $credentials);
        }
        return $this;
    }

    /**
     * Prepares the header for the request.
     * @param Header $header
     * @return array|string[]
     */
    protected function prepareRequestHeader(Header $header): array
    {
        $result = [];
        foreach ($header as $name => $value) {
            $result[] = $name . ': ' . $value;
        }
        return $result;
    }

    /**
     * Executes the requests of the multi cUrl.
     * @return $this Implementing fluent interface.
     */
    protected function executeMultiCurl()
    {
        do {
            $this->multiCurl->execute();
            $this->checkStatusMessages();
        } while ($this->multiCurl->getCurrentExecutionCode() === CURLM_CALL_MULTI_PERFORM);
        return $this;
    }

    /**
     * Checks for any waiting status messages of the cURL requests.
     * @return $this
     */
    protected function checkStatusMessages()
    {
        while (($message = $this->multiCurl->readInfo()) !== false) {
            $this->processCurlResponse((int) $message['result'], $message['handle'])
                 ->executeNextWaitingRequest();
        }
        return $this;
    }

    /**
     * processes the response of the specified curl handle.
     * @param int $statusCode
     * @param resource $curlHandle
     * @return $this
     */
    protected function processCurlResponse(int $statusCode, $curlHandle)
    {
        foreach ($this->runningRequests as $index => $request) {
            if ($request->getCurl()->getHandle() === $curlHandle) {
                $this->parseResponse($request, $statusCode)
                     ->triggerCallback($request->getOnCompleteCallback(), $request);

                $this->multiCurl->removeCurl($request->getCurl());
                unset($this->runningRequests[$index]);
                break;
            }
        }
        return $this;
    }

    /**
     * Creates the response of the specified request.
     * @param Request $request
     * @param int $errorCode
     * @return $this
     */
    protected function parseResponse(Request $request, int $errorCode)
    {
        $curl = $request->getCurl();
        $response = $request->getResponse();

        $response->setErrorCode($errorCode)
                 ->setErrorMessage($curl->getErrorMessage());
        if ($errorCode === CURLE_OK) {
            $this->hydrateResponseFromCurl($response, $curl);
        }
        return $this;
    }

    /**
     * Hydrates the response from the specified cUrl request.
     * @param Response $response
     * @param Curl $curl
     * @return $this
     */
    protected function hydrateResponseFromCurl(Response $response, Curl $curl)
    {
        $headerSize = $curl->getInfo(CURLINFO_HEADER_SIZE);
        $rawContent = $this->multiCurl->getContent($curl);
        $response->setStatusCode($curl->getInfo(CURLINFO_HTTP_CODE))
                 ->setContent(substr($rawContent, $headerSize));
        $this->parseResponseHeaders($response, substr($rawContent, 0, $headerSize));
        return $this;
    }

    /**
     * Parses the header string into the response.
     * @param Response $response
     * @param string $headerString
     * @return $this
     */
    protected function parseResponseHeaders(Response $response, string $headerString)
    {
        foreach (array_filter(explode("\r\n\r\n", $headerString)) as $responseHeader) {
            $header = new Header();
            foreach (explode("\r\n", $responseHeader) as $headerLine) {
                $parts = explode(':', $headerLine, 2);
                if (count($parts) === 2) {
                    $header->set(trim($parts[0]), trim($parts[1]));
                }
            }
            $response->addHeader($header);
        }
        return $this;
    }

    /**
     * Triggers the specified callback, if it is valid.
     * @param callable|null $callback
     * @param Request $request
     * @return $this
     */
    protected function triggerCallback($callback, Request $request)
    {
        if (is_callable($callback)) {
            $callback($request);
        }
        return $this;
    }

    /**
     * Delays the script execution until all requests have been finished.
     * @return $this Implementing fluent interface.
     */
    public function waitForAllRequests()
    {
        while ($this->multiCurl->getStillRunningRequests() > 0
            && $this->multiCurl->getCurrentExecutionCode() === CURLM_OK
        ) {
            $this->multiCurl->select();
            $this->executeMultiCurl();
        }
        return $this;
    }

    /**
     * Delays the script execution until at least the specified request has been finished.
     * @param Request $request
     * @return $this Implementing fluent interface.
     */
    public function waitForSingleRequest(Request $request)
    {
        $this->executeRequest($request);
        while ($this->multiCurl->getStillRunningRequests() > 0
            && $this->multiCurl->getCurrentExecutionCode() === CURLM_OK
            && in_array($request, $this->runningRequests)
        ) {
            $this->multiCurl->select();
            $this->executeMultiCurl();
        }
        return $this;
    }
}
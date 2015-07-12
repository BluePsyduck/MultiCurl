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
     * The multi cUrl instance.
     * @var \BluePsyduck\MultiCurl\Wrapper\MultiCurl
     */
    protected $multiCurl;

    /**
     * Thr requests to execute.
     * @var array|\BluePsyduck\MultiCurl\Entity\Request[]
     */
    protected $requests = array();

    /**
     * Initializes the manager.
     */
    public function __construct() {
        $this->multiCurl = new MultiCurl();
    }

    /**
     * Adds a request to the MultiCurl manager.
     * @param \BluePsyduck\MultiCurl\Entity\Request $request
     * @return $this Implementing fluent interface.
     */
    public function addRequest(Request $request) {
        $curl = new Curl();
        $this->hydrateCurlFromRequest($curl, $request);
        $this->multiCurl->addCurl($curl);
        $request->setCurl($curl);
        $this->requests[] = $request;
        return $this;
    }

    /**
     * Hydrates the cURL instance with the data from the specified request.
     * @param \BluePsyduck\MultiCurl\Wrapper\Curl $curl
     * @param \BluePsyduck\MultiCurl\Entity\Request $request
     * @return $this Implementing fluent interface.
     */
    protected function hydrateCurlFromRequest(Curl $curl, Request $request) {
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
        if ($request->getBasicAuthUsername() && $request->getBasicAuthPassword()) {
            $credentials = $request->getBasicAuthUsername() . ':' . $request->getBasicAuthPassword();
            $curl->setOption(CURLOPT_USERPWD, $credentials);
        }
        return $this;
    }

    /**
     * Executes the requests.
     * @return $this Implementing fluent interface.
     */
    public function execute() {
        do {
            $this->multiCurl->execute();
            $this->checkStatusMessages();
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
                $this->execute();
            }
        }
        return $this;
    }

    /**
     * Checks for any waiting status messages of the cURL requests.
     * @return $this
     */
    protected function checkStatusMessages() {
        while (($message = $this->multiCurl->readInfo()) !== false) {
            $request = $this->findRequestToCurlHandle($message['handle']);
            if ($request instanceof Request) {
                $request->setResponse($this->createResponse($message['result'], $request));
            }
        }
        return $this;
    }

    /**
     * Searches for the request instance with the specified cURL handle.
     * @param resource $handle
     * @return \BluePsyduck\MultiCurl\Entity\Request|null
     */
    protected function findRequestToCurlHandle($handle) {
        $result = null;
        foreach ($this->requests as $request) {
            if ($request->getCurl()->getHandle() === $handle) {
                $result = $request;
                break;
            }
        }
        return $result;
    }

    /**
     * Creates the response of the specified request.
     * @param int $statusCode
     * @param \BluePsyduck\MultiCurl\Entity\Request $request
     * @return \BluePsyduck\MultiCurl\Entity\Response
     */
    protected function createResponse($statusCode, Request $request) {
        $response = new Response();
        $response->setErrorCode($statusCode)
                 ->setErrorMessage($request->getCurl()->getErrorMessage());

        if ($statusCode === CURLE_OK) {
            $this->hydrateResponse($response, $request);
        }

        if (is_callable($request->getOnCompleteCallback())) {
            call_user_func($request->getOnCompleteCallback(), $request);
        }
        $this->multiCurl->removeCurl($request->getCurl());
        return $response;
    }

    /**
     * Hydrates the data from the executed request into the response instance.
     * @param \BluePsyduck\MultiCurl\Entity\Response $response
     * @param \BluePsyduck\MultiCurl\Entity\Request $request
     * @return $this
     */
    protected function hydrateResponse(Response $response, Request $request) {
        $curl = $request->getCurl();

        $headerSize = $curl->getInfo(CURLINFO_HEADER_SIZE);
        $rawContent = $this->multiCurl->getContent($curl);

        $response->setStatusCode($curl->getInfo(CURLINFO_HTTP_CODE))
                 ->setHeaders($this->parseHeaders(substr($rawContent, 0, $headerSize)))
                 ->setContent(substr($rawContent, $headerSize));

        return $this;
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
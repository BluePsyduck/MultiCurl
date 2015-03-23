<?php

namespace BluePsyduckTests\MultiCurl;

use BluePsyduck\MultiCurl\Entity\Request;
use BluePsyduck\MultiCurl\Entity\Response;
use BluePsyduck\MultiCurl\Manager;
use BluePsyduckTests\MultiCurl\Assets\TestCase;

/**
 * PHPUnit test of the MultiCurl manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */
class ManagerTest extends TestCase {
    /**
     * Tests the addRequest() method.
     * @covers \BluePsyduck\MultiCurl\Manager::addRequest
     */
    public function testAddRequest() {
        $request1 = new Request();
        $request1->setUrl('abc');
        $request2 = new Request();
        $request2->setUrl('def');

        $requests = array('ghi' => $request1);
        $expectedRequests = array('ghi' => $request1, 'jkl' => $request2);

        $manager = new Manager();
        $this->injectProperty($manager, 'requests', $requests);
        $result = $manager->addRequest('jkl', $request2);
        $this->assertEquals($manager, $result);
        $this->assertPropertyEquals($expectedRequests, $manager, 'requests');
    }

    /**
     * Tests the execute() method.
     * @covers \BluePsyduck\MultiCurl\Manager::execute
     */
    public function testExecute() {
        $requests = array('abc' => 'def');
        $curls = array('ghi' => 'jkl');

        /* @var $manager \BluePsyduck\MultiCurl\Manager|\PHPUnit_Framework_MockObject_MockObject */
        $manager = $this->getMockBuilder('BluePsyduck\MultiCurl\Manager')
                        ->setMethods(array('initializeMultiCurl', 'start'))
                        ->getMock();
        $manager->expects($this->at(0))
                ->method('initializeMultiCurl')
                ->with($requests, $this->isInstanceOf('BluePsyduck\MultiCurl\Wrapper\MultiCurl'))
                ->willReturn($curls);
        $manager->expects($this->at(1))
                ->method('start')
                ->willReturnSelf();

        $this->injectProperty($manager, 'requests', $requests);

        $result = $manager->execute();
        $this->assertEquals($manager, $result);
        $this->assertPropertyInstanceOf('BluePsyduck\MultiCurl\Wrapper\MultiCurl', $manager, 'multiCurl');
        $this->assertPropertyEquals($curls, $manager, 'curls');
    }

    /**
     * Tests the initializeMultiCurl() method.
     * @covers \BluePsyduck\MultiCurl\Manager::initializeMultiCurl
     */
    public function testInitializeMultiCurl() {
        $request1 = new Request();
        $request1->setUrl('abc');
        $request2 = new Request();
        $request2->setUrl('def');
        $requests = array('foo' => $request1, 'bar' => $request2);

        /* @var $multiCurl \BluePsyduck\MultiCurl\Wrapper\MultiCurl|\PHPUnit_Framework_MockObject_MockObject */
        $multiCurl = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\MultiCurl')
                          ->setMethods(array('__construct', '__destruct', 'addCurl'))
                          ->disableOriginalConstructor()
                          ->getMock();
        $multiCurl->expects($this->exactly(2))
                  ->method('addCurl')
                  ->with($this->isInstanceOf('BluePsyduck\MultiCurl\Wrapper\Curl'));

        /* @var $manager \BluePsyduck\MultiCurl\Manager|\PHPUnit_Framework_MockObject_MockObject */
        $manager = $this->getMockBuilder('BluePsyduck\MultiCurl\Manager')
                        ->setMethods(array('initializeCurlForRequest'))
                        ->getMock();
        $manager->expects($this->at(0))
                ->method('initializeCurlForRequest')
                ->with($request1, $this->isInstanceOf('BluePsyduck\MultiCurl\Wrapper\Curl'));
        $manager->expects($this->at(1))
                ->method('initializeCurlForRequest')
                ->with($request2, $this->isInstanceOf('BluePsyduck\MultiCurl\Wrapper\Curl'));

        $result = $this->invokeMethod($manager, 'initializeMultiCurl', array($requests, $multiCurl));
        $this->assertEquals(2, count($result));
        $this->assertInstanceOf('BluePsyduck\MultiCurl\Wrapper\Curl', $result['foo']);
        $this->assertInstanceOf('BluePsyduck\MultiCurl\Wrapper\Curl', $result['bar']);
    }

    /**
     * Provides the data for the initializeCurlForRequest() test.
     * @return array The data.
     */
    public function provideInitializeCurlForRequest() {
        $request1 = new Request();
        $request1->setUrl('abc')
                 ->setMethod(Request::METHOD_GET);
        $options1 = array(
            CURLOPT_HTTPGET => true,
            CURLOPT_URL => 'abc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true
        );

        $request2 = new Request();
        $request2->setUrl('def')
                 ->setMethod(Request::METHOD_POST)
                 ->setRequestData('ghi=jkl')
                 ->setTimeout(1337)
                 ->setHeaders(array('mno'));
        $options2 = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'ghi=jkl',
            CURLOPT_URL => 'def',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 1337,
            CURLOPT_HTTPHEADER => array('mno')
        );
        return array(
            array($request1, $options1),
            array($request2, $options2)
        );
    }

    /**
     * Tests the initializeCurlForRequest() method.
     * @param Request $request The request to use.
     * @param array $expectedOptions The expected options to be set in the Curl instance.
     * @covers \BluePsyduck\MultiCurl\Manager::initializeCurlForRequest
     * @dataProvider provideInitializeCurlForRequest
     */
    public function testInitializeCurlForRequest(Request $request, array $expectedOptions) {
        /* @var $curl \BluePsyduck\MultiCurl\Wrapper\Curl|\PHPUnit_Framework_MockObject_MockObject */
        $curl = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\Curl')
                     ->setMethods(array('__construct', '__destruct', 'setOption'))
                     ->disableOriginalConstructor()
                     ->getMock();

        $index = 0;
        foreach ($expectedOptions as $name => $value) {
            $curl->expects($this->at($index))
                 ->method('setOption')
                 ->with($name, $value)
                 ->willReturnSelf();
            ++$index;
        }

        $manager = new Manager();
        $result = $this->invokeMethod($manager, 'initializeCurlForRequest', array($request, $curl));
        $this->assertEquals($manager, $result);
    }

    /**
     * Tests the start() method.
     * @covers \BluePsyduck\MultiCurl\Manager::start
     */
    public function testStart() {
        /* @var $multiCurl \BluePsyduck\MultiCurl\Wrapper\MultiCurl|\PHPUnit_Framework_MockObject_MockObject */
        $multiCurl = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\MultiCurl')
                          ->setMethods(array('execute', 'getCurrentExecutionCode', '__construct', '__destruct'))
                          ->disableOriginalConstructor()
                          ->getMock();
        $multiCurl->expects($this->at(0))
                  ->method('execute');
        $multiCurl->expects($this->at(1))
                  ->method('getCurrentExecutionCode')
                  ->willReturn(CURLM_CALL_MULTI_PERFORM);
        $multiCurl->expects($this->at(2))
                  ->method('execute');
        $multiCurl->expects($this->at(3))
                  ->method('getCurrentExecutionCode')
                  ->willReturn(CURLM_OK);

        $manager = new Manager();
        $this->injectProperty($manager, 'multiCurl', $multiCurl);
        $result = $this->invokeMethod($manager, 'start');
        $this->assertEquals($manager, $result);
    }

    /**
     * Provides the data for the waitForRequests() test.
     * @return array The data.
     */
    public function provideWaitForRequests() {
        return array(
            array(
                array(
                    array('getStillRunningRequests', 0)
                ),
                0
            ),
            array(
                array(
                    array('getStillRunningRequests', 42),
                    array('getCurrentExecutionCode', CURLM_BAD_HANDLE)
                ),
                0
            ),
            array(
                array(
                    array('getStillRunningRequests', 42),
                    array('getCurrentExecutionCode', CURLM_OK),
                    array('select', -1),
                    array('getStillRunningRequests', 0),
                ),
                0
            ),
            array(
                array(
                    array('getStillRunningRequests', 42),
                    array('getCurrentExecutionCode', CURLM_OK),
                    array('select', 1337),
                    array('getStillRunningRequests', 0),
                ),
                1
            ),
            array(
                array(
                    array('getStillRunningRequests', 42),
                    array('getCurrentExecutionCode', CURLM_OK),
                    array('select', 1337),
                    array('getStillRunningRequests', 42),
                    array('getCurrentExecutionCode', CURLM_OK),
                    array('select', 1337),
                    array('getStillRunningRequests', 0),
                ),
                2
            )
        );
    }

    /**
     * Tests the waitForRequests() method.
     * @param array $multiCurlInvocations The invocations of the MultiCurl wrapper.
     * @param int $expectedStartCount The number of times to expect start() to be called.
     * @covers \BluePsyduck\MultiCurl\Manager::waitForRequests
     * @dataProvider provideWaitForRequests
     */
    public function testWaitForRequests(array $multiCurlInvocations, $expectedStartCount) {
        /* @var $multiCurl \BluePsyduck\MultiCurl\Wrapper\MultiCurl|\PHPUnit_Framework_MockObject_MockObject */
        $multiCurl = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\MultiCurl')
                          ->setMethods(array(
                              '__construct', '__destruct', 'getStillRunningRequests', 'getCurrentExecutionCode',
                              'select'
                          ))
                          ->disableOriginalConstructor()
                          ->getMock();

        foreach ($multiCurlInvocations as $index => $invocation) {
            $multiCurl->expects($this->at($index))
                      ->method(array_shift($invocation))
                      ->willReturn(array_shift($invocation));
        }

        /* @var $manager \BluePsyduck\MultiCurl\Manager|\PHPUnit_Framework_MockObject_MockObject */
        $manager = $this->getMockBuilder('BluePsyduck\MultiCurl\Manager')
                        ->setMethods(array('start'))
                        ->getMock();
        $manager->expects($this->exactly($expectedStartCount))
                ->method('start');
        $this->injectProperty($manager, 'multiCurl', $multiCurl);

        $result = $manager->waitForRequests();
        $this->assertEquals($manager, $result);
    }

    /**
     * Provides the data for the getResponse() test.
     * @return array The data.
     */
    public function provideGetResponse() {
        $request1 = new Request();
        $request1->setUrl('abc');
        $request2 = new Request();
        $request2->setUrl('def');

        $response1 = new Response();
        $response1->setContent('ghi');
        $response2 = new Response();
        $response2->setContent('jkl');

        return array(
            array(
                'foo',
                array(),
                array(),
                null,
                array(),
                null
            ),
            array(
                'foo',
                array('foo' => $request1, 'bar' => $request2),
                array('bar' => $response2),
                $response1,
                array('foo' => $response1, 'bar' => $response2),
                $response1
            ),
            array(
                'foo',
                array('foo' => $request1, 'bar' => $request2),
                array('foo' => $response1, 'bar' => $response2),
                null,
                array('foo' => $response1, 'bar' => $response2),
                $response1
            )
        );
    }

    /**
     * Tests the getResponse() method.
     * @param string $name The name to use.
     * @param array $requests The requests to set.
     * @param array $responses The responses to set.
     * @param \BluePsyduck\MultiCurl\Entity\Response|null $resultParseResponse The response to be returned from
     * parseResponse(), or null if not called.
     * @param array $expectedResponses The responses array to be expected in the manager.
     * @param \BluePsyduck\MultiCurl\Entity\Response|null $expectedResult The expected result.
     * @covers \BluePsyduck\MultiCurl\Manager::getResponse
     * @dataProvider provideGetResponse
     */
    public function testGetResponse(
        $name, $requests, $responses, $resultParseResponse, $expectedResponses, $expectedResult
    ) {
        /* @var $manager \BluePsyduck\MultiCurl\Manager|\PHPUnit_Framework_MockObject_MockObject */
        $manager = $this->getMockBuilder('BluePsyduck\MultiCurl\Manager')
                        ->setMethods(array('parseResponse'))
                        ->getMock();
        $manager->expects(is_null($resultParseResponse) ? $this->never() : $this->once())
                ->method('parseResponse')
                ->with($name)
                ->willReturn($resultParseResponse);

        $this->injectProperty($manager, 'requests', $requests)
             ->injectProperty($manager, 'responses', $responses);

        $result = $manager->getResponse($name);
        $this->assertEquals($expectedResult, $result);
        $this->assertPropertyEquals($expectedResponses, $manager, 'responses');
    }

    /**
     * Tests the parseResponse() method.
     * @covers \BluePsyduck\MultiCurl\Manager::parseResponse
     */
    public function testParseResponse() {
        $name = 'foo';
        $rawContent = 'abcdef';
        $rawHeader = 'abc';
        $headers = array('abc');
        $content = 'def';
        $statusCode = 42;

        /* @var $curl \BluePsyduck\MultiCurl\Wrapper\Curl|\PHPUnit_Framework_MockObject_MockObject */
        $curl = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\Curl')
                     ->setMethods(array('__construct', '__destruct', 'getInfo'))
                     ->disableOriginalConstructor()
                     ->getMock();
        $curl->expects($this->at(0))
             ->method('getInfo')
             ->with(CURLINFO_HEADER_SIZE)
             ->willReturn(strlen($rawHeader));
        $curl->expects($this->at(1))
             ->method('getInfo')
             ->with(CURLINFO_HTTP_CODE)
             ->willReturn($statusCode);

        /* @var $multiCurl \BluePsyduck\MultiCurl\Wrapper\MultiCurl|\PHPUnit_Framework_MockObject_MockObject */
        $multiCurl = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\MultiCurl')
                          ->setMethods(array('__construct', '__destruct', 'getContent', 'removeCurl'))
                          ->disableOriginalConstructor()
                          ->getMock();
        $multiCurl->expects($this->at(0))
                  ->method('getContent')
                  ->with($curl)
                  ->willReturn($rawContent);
        $multiCurl->expects($this->at(1))
                  ->method('removeCurl')
                  ->with($curl);

        /* @var $manager \BluePsyduck\MultiCurl\Manager|\PHPUnit_Framework_MockObject_MockObject */
        $manager = $this->getMockBuilder('BluePsyduck\MultiCurl\Manager')
                        ->setMethods(array('parseHeaders'))
                        ->getMock();
        $manager->expects($this->once())
                ->method('parseHeaders')
                ->with($rawHeader)
                ->willReturn($headers);
        $this->injectProperty($manager, 'multiCurl', $multiCurl)
             ->injectProperty($manager, 'curls', array($name => $curl));

        $result = $this->invokeMethod($manager, 'parseResponse', array($name));
        $this->assertInstanceOf('BluePsyduck\MultiCurl\Entity\Response', $result);
        /* @var \BluePsyduck\MultiCurl\Entity\Response $result */
        $this->assertEquals($statusCode, $result->getStatusCode());
        $this->assertEquals($headers, $result->getHeaders());
        $this->assertEquals($content, $result->getContent());
        $this->assertPropertyEquals(array(), $manager, 'curls');
    }

    /**
     * Tests the parseHeaders() method.
     * @covers \BluePsyduck\MultiCurl\Manager::parseHeaders
     */
    public function testParseHeaders() {
        $headerString = "HTTP/1.1 200 OK\r\nContent-Type: Foo\r\nabc:def";
        $expectedResult = array(
            'Content-Type' => 'Foo',
            'abc' => 'def'
        );

        $manager = new Manager();
        $result = $this->invokeMethod($manager, 'parseHeaders', array($headerString));
        $this->assertEquals($expectedResult, $result);
    }
}

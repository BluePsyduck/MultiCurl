<?php

namespace BluePsyduckTests\MultiCurl;

use BluePsyduck\MultiCurl\Entity\Request;
use BluePsyduck\MultiCurl\Entity\Response;
use BluePsyduck\MultiCurl\Manager;
use BluePsyduck\MultiCurl\Wrapper\Curl;
use BluePsyduckTests\MultiCurl\Assets\TestCase;

/**
 * PHPUnit test of the MultiCurl manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */
class ManagerTest extends TestCase {
    /**
     * Tests the __construct() method.
     * @covers \BluePsyduck\MultiCurl\Manager::__construct
     */
    public function testConstruct() {
        $manager = new Manager();
        $this->assertPropertyInstanceOf('BluePsyduck\MultiCurl\Wrapper\MultiCurl', $manager, 'multiCurl');
    }
    
    /**
     * Tests the addRequest() method.
     * @covers \BluePsyduck\MultiCurl\Manager::addRequest
     */
    public function testAddRequest() {
        /* @var $request1 \BluePsyduck\MultiCurl\Entity\Request|\PHPUnit_Framework_MockObject_MockObject */
        $request1 = $this->getMockBuilder('BluePsyduck\MultiCurl\Entity\Request')
                         ->setMethods(array('setCurl'))
                         ->getMock();
        $request1->expects($this->once())
                 ->method('setCurl')
                 ->with($this->isInstanceOf('BluePsyduck\MultiCurl\Wrapper\Curl'));
        $request1->setUrl('abc');

        $request2 = new Request();
        $request2->setUrl('def');

        $requests = array($request2);
        $expectedRequests = array($request2, $request1);

        /* @var $multiCurl \BluePsyduck\MultiCurl\Wrapper\MultiCurl|\PHPUnit_Framework_MockObject_MockObject */
        $multiCurl = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\MultiCurl')
                          ->setMethods(array('addCurl'))
                          ->getMock();
        $multiCurl->expects($this->once())
                  ->method('addCurl')
                  ->with($this->isInstanceOf('BluePsyduck\MultiCurl\Wrapper\Curl'));

        /* @var $manager \BluePsyduck\MultiCurl\Manager|\PHPUnit_Framework_MockObject_MockObject */
        $manager = $this->getMockBuilder('BluePsyduck\MultiCurl\Manager')
                        ->setMethods(array('hydrateCurlFromRequest'))
                        ->getMock();
        $manager->expects($this->once())
                ->method('hydrateCurlFromRequest')
                ->with($this->isInstanceOf('BluePsyduck\MultiCurl\Wrapper\Curl'), $request1);
        $this->injectProperty($manager, 'multiCurl', $multiCurl)
             ->injectProperty($manager, 'requests', $requests);
        $result = $manager->addRequest($request1);
        $this->assertEquals($manager, $result);
        $this->assertPropertyEquals($expectedRequests, $manager, 'requests');
    }

    /**
     * Tests the execute() method.
     * @covers \BluePsyduck\MultiCurl\Manager::execute
     */
    public function testExecute() {
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


        /* @var $manager \BluePsyduck\MultiCurl\Manager|\PHPUnit_Framework_MockObject_MockObject */
        $manager = $this->getMockBuilder('BluePsyduck\MultiCurl\Manager')
                        ->setMethods(array('checkStatusMessages'))
                        ->getMock();
        $manager->expects($this->exactly(2))
                ->method('checkStatusMessages');
        $this->injectProperty($manager, 'multiCurl', $multiCurl);
        $result = $manager->execute();
        $this->assertEquals($manager, $result);
    }

    /**
     * Provides the data for the hydrateCurlFromRequest() test.
     * @return array The data.
     */
    public function provideHydrateCurlFromRequest() {
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
                 ->setHeaders(array('mno'))
                 ->setBasicAuth('pqr', 'stu');
        $options2 = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'ghi=jkl',
            CURLOPT_URL => 'def',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 1337,
            CURLOPT_HTTPHEADER => array('mno'),
            CURLOPT_USERPWD => 'pqr:stu'
        );
        return array(
            array($request1, $options1),
            array($request2, $options2)
        );
    }

    /**
     * Tests the hydrateCurlFromRequest() method.
     * @param Request $request The request to use.
     * @param array $expectedOptions The expected options to be set in the Curl instance.
     * @covers \BluePsyduck\MultiCurl\Manager::hydrateCurlFromRequest
     * @dataProvider provideHydrateCurlFromRequest
     */
    public function testHydrateCurlFromRequest(Request $request, array $expectedOptions) {
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
        $result = $this->invokeMethod($manager, 'hydrateCurlFromRequest', array($curl, $request));
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
                1
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
     * @param int $expectedExecuteCount The number of times to expect start() to be called.
     * @covers \BluePsyduck\MultiCurl\Manager::waitForRequests
     * @dataProvider provideWaitForRequests
     */
    public function testWaitForRequests(array $multiCurlInvocations, $expectedExecuteCount) {
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
                        ->setMethods(array('execute'))
                        ->getMock();
        $manager->expects($this->exactly($expectedExecuteCount))
                ->method('execute');
        $this->injectProperty($manager, 'multiCurl', $multiCurl);

        $result = $manager->waitForRequests();
        $this->assertEquals($manager, $result);
    }
    
    /**
     * Tests the checkStatusMessages() method.
     * @covers \BluePsyduck\MultiCurl\Manager::checkStatusMessages
     */
    public function testCheckStatusMessages() {
        $handle1 = 'abc';
        $handle2 = 'def';
        $response = new Response();
        $response->setContent('ghi');

        /* @var $request \BluePsyduck\MultiCurl\Entity\Request|\PHPUnit_Framework_MockObject_MockObject */
        $request = $this->getMockBuilder('BluePsyduck\MultiCurl\Entity\Request')
                        ->setMethods(array('setResponse'))
                        ->getMock();
        $request->expects($this->once())
                ->method('setResponse')
                ->with($response);

        /* @var $multiCurl \BluePsyduck\MultiCurl\Wrapper\MultiCurl|\PHPUnit_Framework_MockObject_MockObject */
        $multiCurl = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\MultiCurl')
                          ->setMethods(array('readInfo'))
                          ->getMock();
        $multiCurl->expects($this->at(0))
                  ->method('readInfo')
                  ->willReturn(array('handle' => $handle1, 'result' => 42));
        $multiCurl->expects($this->at(1))
                  ->method('readInfo')
                  ->willReturn(array('handle' => $handle2, 'result' => 1337));
        $multiCurl->expects($this->at(2))
                  ->method('readInfo')
                  ->willReturn(false);

        /* @var $manager \BluePsyduck\MultiCurl\Manager|\PHPUnit_Framework_MockObject_MockObject */
        $manager = $this->getMockBuilder('BluePsyduck\MultiCurl\Manager')
                        ->setMethods(array('findRequestToCurlHandle', 'createResponse'))
                        ->getMock();
        $manager->expects($this->at(0))
                ->method('findRequestToCurlHandle')
                ->with($handle1)
                ->willReturn(null);
        $manager->expects($this->at(1))
                ->method('findRequestToCurlHandle')
                ->with($handle2)
                ->willReturn($request);
        $manager->expects($this->at(2))
                ->method('createResponse')
                ->with(1337, $request)
                ->willReturn($response);
        $this->injectProperty($manager, 'multiCurl', $multiCurl);

        $result = $this->invokeMethod($manager, 'checkStatusMessages');
        $this->assertEquals($manager, $result);
    }

    /**
     * Provides the data for the findRequestToCurlHandle() test.
     * @return array The data.
     */
    public function provideFindRequestToCurlHandle() {
        $request1 = new Request();
        $request1->setUrl('abc')
                 ->setCurl(new Curl());
        $request2 = new Request();
        $request2->setUrl('def')
                 ->setCurl(new Curl());

        return array(
            array(array($request1, $request2), $request1->getCurl()->getHandle(), $request1),
            array(array($request1, $request2), 'ghi', null),
        );
    }

    /**
     * Tests the findRequestToCurlHandle() method.
     * @param array|\BluePsyduck\MultiCurl\Entity\Request[] $requests The requests to set.
     * @param resource $handle The handle to use.
     * @param \BluePsyduck\MultiCurl\Entity\Request|null $expectedResult The expected result.
     * @covers \BluePsyduck\MultiCurl\Manager::findRequestToCurlHandle
     * @dataProvider provideFindRequestToCurlHandle
     */
    public function testFindRequestToCurlHandle($requests, $handle, $expectedResult) {
        $manager = new Manager();
        $this->injectProperty($manager, 'requests', $requests);
        $result = $this->invokeMethod($manager, 'findRequestToCurlHandle', array($handle));
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the createResponse() test.
     * @return array The data.
     */
    public function provideCreateResponse() {
        /* @var $curl1 \BluePsyduck\MultiCurl\Wrapper\Curl|\PHPUnit_Framework_MockObject_MockObject */
        $curl1 = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\Curl')
                      ->setMethods(array('getErrorMessage'))
                      ->getMock();
        $curl1->expects($this->once())
              ->method('getErrorMessage')
              ->willReturn('abc');
        $request1 = new Request();
        $request1->setCurl($curl1);

        /* @var $curl2 \BluePsyduck\MultiCurl\Wrapper\Curl|\PHPUnit_Framework_MockObject_MockObject */
        $curl2 = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\Curl')
                      ->setMethods(array('getErrorMessage'))
                      ->getMock();
        $curl2->expects($this->once())
              ->method('getErrorMessage')
              ->willReturn('def');
        /* @var $request2 \BluePsyduck\MultiCurl\Entity\Request|\PHPUnit_Framework_MockObject_MockObject */
        $request2 = $this->getMockBuilder('BluePsyduck\MultiCurl\Entity\Request')
                         ->setMethods(array('onComplete'))
                         ->getMock();
        $request2->expects($this->once())
                 ->method('onComplete')
                 ->with($request2);
        $request2->setCurl($curl2)
                 ->setOnCompleteCallback(array($request2, 'onComplete'));

        return array(
            array(CURLE_OK, $request1, true, CURLE_OK, 'abc'),
            array(42, $request2, false, 42, 'def')
        );
    }

    /**
     * Tests the createResponse() method.
     * @param int $statusCode The status code to use.
     * @param \BluePsyduck\MultiCurl\Entity\Request $request The request to use.
     * @param bool $expectHydrateResponse Whether to expect hydrateResponse() to be called.
     * @param int $expectedErrorCode The expected status code.
     * @param string $expectedErrorMessage The expected error message.
     * @covers \BluePsyduck\MultiCurl\Manager::createResponse
     * @dataProvider provideCreateResponse
     */
    public function testCreateResponse(
        $statusCode, $request, $expectHydrateResponse, $expectedErrorCode, $expectedErrorMessage
    ) {
        /* @var $multiCurl \BluePsyduck\MultiCurl\Wrapper\MultiCurl|\PHPUnit_Framework_MockObject_MockObject */
        $multiCurl = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\MultiCurl')
                          ->setMethods(array('removeCurl'))
                          ->getMock();
        $multiCurl->expects($this->once())
                  ->method('removeCurl')
                  ->with($request->getCurl());

        /* @var $manager \BluePsyduck\MultiCurl\Manager|\PHPUnit_Framework_MockObject_MockObject */
        $manager = $this->getMockBuilder('BluePsyduck\MultiCurl\Manager')
                        ->setMethods(array('hydrateResponse'))
                        ->getMock();
        $manager->expects($expectHydrateResponse ? $this->once() : $this->never())
                ->method('hydrateResponse')
                ->with($this->isInstanceOf('BluePsyduck\MultiCurl\Entity\Response'), $request);
        $this->injectProperty($manager, 'multiCurl', $multiCurl);

        $result = $this->invokeMethod($manager, 'createResponse', array($statusCode, $request));
        $this->assertInstanceOf('BluePsyduck\MultiCurl\Entity\Response', $result);
        /* @var \BluePsyduck\MultiCurl\Entity\Response $result */
        $this->assertEquals($expectedErrorCode, $result->getErrorCode());
        $this->assertEquals($expectedErrorMessage, $result->getErrorMessage());
    }
    
    /**
     * Tests the hydrateResponse() method.
     * @covers \BluePsyduck\MultiCurl\Manager::hydrateResponse
     */
    public function testParseResponse() {
        $rawContent = 'abcdef';
        $rawHeader = 'abc';
        $headers = array('abc');
        $content = 'def';
        $statusCode = 42;

        /* @var $curl \BluePsyduck\MultiCurl\Wrapper\Curl|\PHPUnit_Framework_MockObject_MockObject */
        $curl = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\Curl')
                     ->setMethods(array('getInfo'))
                     ->getMock();
        $curl->expects($this->at(0))
             ->method('getInfo')
             ->with(CURLINFO_HEADER_SIZE)
             ->willReturn(strlen($rawHeader));
        $curl->expects($this->at(1))
             ->method('getInfo')
             ->with(CURLINFO_HTTP_CODE)
             ->willReturn($statusCode);

        $request = new Request();
        $request->setCurl($curl);

        /* @var $response \BluePsyduck\MultiCurl\Entity\Response|\PHPUnit_Framework_MockObject_MockObject */
        $response = $this->getMockBuilder('BluePsyduck\MultiCurl\Entity\Response')
                         ->setMethods(array('setStatusCode', 'setHeaders', 'setContent'))
                         ->getMock();
        $response->expects($this->once())
                 ->method('setStatusCode')
                 ->with($statusCode)
                 ->willReturnSelf();
        $response->expects($this->once())
                 ->method('setHeaders')
                 ->with($headers)
                 ->willReturnSelf();
        $response->expects($this->once())
                 ->method('setContent')
                 ->with($content)
                 ->willReturnSelf();

        /* @var $multiCurl \BluePsyduck\MultiCurl\Wrapper\MultiCurl|\PHPUnit_Framework_MockObject_MockObject */
        $multiCurl = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\MultiCurl')
                          ->setMethods(array('getContent'))
                          ->getMock();
        $multiCurl->expects($this->once())
                  ->method('getContent')
                  ->with($curl)
                  ->willReturn($rawContent);

        /* @var $manager \BluePsyduck\MultiCurl\Manager|\PHPUnit_Framework_MockObject_MockObject */
        $manager = $this->getMockBuilder('BluePsyduck\MultiCurl\Manager')
                        ->setMethods(array('parseHeaders'))
                        ->getMock();
        $manager->expects($this->once())
                ->method('parseHeaders')
                ->with($rawHeader)
                ->willReturn($headers);
        $this->injectProperty($manager, 'multiCurl', $multiCurl);

        $result = $this->invokeMethod($manager, 'hydrateResponse', array($response, $request));
        $this->assertEquals($manager, $result);
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

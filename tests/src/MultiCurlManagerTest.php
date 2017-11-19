<?php

namespace BluePsyduckTests\MultiCurl;

use BluePsyduck\MultiCurl\Entity\Header;
use BluePsyduck\MultiCurl\Entity\Request;
use BluePsyduck\MultiCurl\Entity\Response;
use BluePsyduck\MultiCurl\MultiCurlManager;
use BluePsyduck\MultiCurl\Wrapper\Curl;
use BluePsyduck\MultiCurl\Wrapper\MultiCurl;
use BluePsyduckTestAssets\MultiCurl\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * PHPUnit test of the MultiCurl manager.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 *
 * @coversDefaultClass \BluePsyduck\MultiCurl\MultiCurlManager
 */
class MultiCurlManagerTest extends TestCase
{
    /**
     * Creates a request with a unique value.
     * @param string $uniqueValue
     * @return Request
     */
    protected function createRequest(string $uniqueValue): Request
    {
        $result = new Request();
        $result->setUrl($uniqueValue);
        return $result;
    }

    /**
     * Tests the __construct() method.
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $manager = new MultiCurlManager();
        $this->assertPropertyInstanceOf(MultiCurl::class, $manager, 'multiCurl');
    }

    /**
     * Tests the setNumberOfParallelRequests() method.
     * @covers ::setNumberOfParallelRequests
     */
    public function testSetNumberOfParallelRequests()
    {
        $expected = 42;
        $manager = new MultiCurlManager();
        $result = $manager->setNumberOfParallelRequests($expected);
        $this->assertEquals($manager, $result);
        $this->assertPropertyEquals($expected, $manager, 'numberOfParallelRequests');
    }

    /**
     * Tests the addRequest() method.
     * @covers ::addRequest
     */
    public function testAddRequest()
    {
        $request1 = $this->createRequest('abc');
        $request2 = $this->createRequest('def');

        $waitingRequests = [$request1];
        $expectedWaitingRequests = [$request1, $request2];

        /* @var MultiCurlManager|MockObject $manager */
        $manager = $this->getMockBuilder(MultiCurlManager::class)
                        ->setMethods(['executeNextWaitingRequest'])
                        ->getMock();
        $manager->expects($this->once())
                ->method('executeNextWaitingRequest');
        $this->injectProperty($manager, 'waitingRequests', $waitingRequests);

        $result = $manager->addRequest($request2);
        $this->assertEquals($manager, $result);
        $this->assertPropertyEquals($expectedWaitingRequests, $manager, 'waitingRequests');
    }

    /**
     * Provides the data for the executeNextWaitingRequest() test.
     * @return array The data.
     */
    public function provideExecuteNextWaitingRequest(): array
    {
        $request1 = $this->createRequest('abc');
        $request2 = $this->createRequest('def');
        $request3 = $this->createRequest('ghi');
        $request4 = $this->createRequest('jkl');
        $request5 = $this->createRequest('mno');

        return [
            [0, [], [], null],
            [0, [$request1], [$request2, $request3, $request4], $request1],
            [0, [$request1, $request2], [$request3, $request4, $request5], $request1],

            [3, [$request1, $request2], [], $request1],
            [3, [$request1, $request2], [$request3, $request4], $request1],
            [3, [$request1, $request2], [$request3, $request4, $request5], null],
        ];
    }

    /**
     * Tests the executeNextWaitingRequest() method.
     * @param int $numberOfParallelRequests
     * @param array $waitingRequests
     * @param array $runningRequests
     * @param Request|null $expectExecuteRequest
     * @covers ::executeNextWaitingRequest
     * @dataProvider provideExecuteNextWaitingRequest
     */
    public function testExecuteNextWaitingRequest(
        int $numberOfParallelRequests,
        array $waitingRequests,
        array $runningRequests,
        $expectExecuteRequest
    ) {
        /* @var MultiCurlManager|MockObject $manager */
        $manager = $this->getMockBuilder(MultiCurlManager::class)
                        ->setMethods(['executeRequest'])
                        ->getMock();
        $manager->expects(is_null($expectExecuteRequest) ? $this->never() : $this->once())
                ->method('executeRequest')
                ->with($expectExecuteRequest);
        $this->injectProperty($manager, 'numberOfParallelRequests', $numberOfParallelRequests)
             ->injectProperty($manager, 'waitingRequests', $waitingRequests)
             ->injectProperty($manager, 'runningRequests', $runningRequests);

        $result = $this->invokeMethod($manager, 'executeNextWaitingRequest');
        $this->assertEquals($manager, $result);
    }

    /**
     * Provides the data for the executeRequest() test.
     * @return array The data.
     */
    public function provideExecuteRequest(): array
    {
        $request1 = $this->createRequest('abc');
        $request2 = $this->createRequest('def');
        $request3 = $this->createRequest('ghi');

        return [
            [[$request1, $request2], [$request3], $request1, true, [1 => $request2], [$request3, $request1]],
            [[$request1], [$request2], $request3, false, [$request1], [$request2]]
        ];
    }

    /**
     * Tests the executeRequest() method.
     * @param array|Request[] $waitingRequests
     * @param array|Request[] $runningRequests
     * @param Request $request
     * @param bool $expectMethodCalls
     * @param array|Request[] $expectedWaitingRequests
     * @param array|Request[] $expectedRunningRequests
     * @covers ::executeRequest
     * @dataProvider provideExecuteRequest
     */
    public function testExecuteRequest(
        array $waitingRequests,
        array $runningRequests,
        Request $request,
        bool $expectMethodCalls,
        array $expectedWaitingRequests,
        array $expectedRunningRequests
    )
    {
        $callback = 'time';
        $request->setOnInitializeCallback($callback);

        /* @var MultiCurl|MockObject $multiCurl */
        $multiCurl = $this->getMockBuilder(MultiCurl::class)
                          ->setMethods(['addCurl'])
                          ->getMock();
        $multiCurl->expects($expectMethodCalls ? $this->once() : $this->never())
                  ->method('addCurl')
                  ->with($request->getCurl());

        /* @var MultiCurlManager|MockObject $manager */
        $manager = $this->getMockBuilder(MultiCurlManager::class)
                        ->setMethods(['hydrateCurlFromRequest', 'triggerCallback', 'executeMultiCurl'])
                        ->getMock();
        $manager->expects($expectMethodCalls ? $this->at(0) : $this->never())
                ->method('hydrateCurlFromRequest')
                ->with($request)
                ->willReturnSelf();
        $manager->expects($expectMethodCalls ? $this->at(1) : $this->never())
                ->method('triggerCallback')
                ->with($callback, $request);
        $manager->expects($expectMethodCalls ? $this->at(2) : $this->never())
                ->method('executeMultiCurl');
        $this->injectProperty($manager, 'multiCurl', $multiCurl)
             ->injectProperty($manager, 'waitingRequests', $waitingRequests)
             ->injectProperty($manager, 'runningRequests', $runningRequests);

        $result = $this->invokeMethod($manager, 'executeRequest', [$request]);
        $this->assertEquals($manager, $result);
        $this->assertPropertyEquals($expectedWaitingRequests, $manager, 'waitingRequests');
        $this->assertPropertyEquals($expectedRunningRequests, $manager, 'runningRequests');
    }

    /**
     * Provides the data for the hydrateCurlFromRequest() test.
     * @return array The data.
     */
    public function provideHydrateCurlFromRequest(): array
    {
        $request1 = new Request();
        $request1->setMethod('ABC')
                 ->setUrl('def');
        $options1 = [
            CURLOPT_CUSTOMREQUEST => 'ABC',
            CURLOPT_URL => 'def',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => []
        ];

        $request2 = new Request();
        $request2->setMethod('GHI')
                 ->setUrl('jkl')
                 ->setRequestData('mno')
                 ->setTimeout(42)
                 ->setBasicAuth('pqr', 'stu');
        $request2->getHeader()->set('vwx', 'yz');
        $options2 = [
            CURLOPT_CUSTOMREQUEST => 'GHI',
            CURLOPT_URL => 'jkl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => ['vwx: yz'],
            CURLOPT_POSTFIELDS => 'mno',
            CURLOPT_TIMEOUT => 42,
            CURLOPT_USERPWD => 'pqr:stu'
        ];

        return [
            [$request1, [], $options1],
            [$request2, ['vwx: yz'], $options2]
        ];
    }

    /**
     * Tests the hydrateCurlFromRequest() method.
     * @param Request $request
     * @param array $preparedHeader
     * @param array $expectedOptions
     * @covers ::hydrateCurlFromRequest
     * @dataProvider provideHydrateCurlFromRequest
     */
    public function testHydrateCurlFromRequest(Request $request, array $preparedHeader, array $expectedOptions)
    {
        /* @var Curl|MockObject $curl */
        $curl = $this->getMockBuilder(Curl::class)
                     ->setMethods(['setOption'])
                     ->getMock();
        $index = 0;
        foreach ($expectedOptions as $name => $value) {
            $curl->expects($this->at($index))
                 ->method('setOption')
                 ->with($name, $value)
                 ->willReturnSelf();
            ++$index;
        }

        /* @var MultiCurlManager|MockObject $manager */
        $manager = $this->getMockBuilder(MultiCurlManager::class)
                        ->setMethods(['prepareRequestHeader'])
                        ->getMock();
        $manager->expects($this->once())
                ->method('prepareRequestHeader')
                ->with($request->getHeader())
                ->willReturn($preparedHeader);

        $result = $this->invokeMethod($manager, 'hydrateCurlFromRequest', [$request, $curl]);
        $this->assertEquals($manager, $result);
    }

    /**
     * Tests the prepareRequestHeader() method.
     * @covers ::prepareRequestHeader
     */
    public function testPrepareRequestHeader()
    {
        $header = new Header();
        $header->set('abc', 'def')
               ->set('ghi', 'jkl');
        $expectedResult = ['abc: def', 'ghi: jkl'];

        $manager = new MultiCurlManager();
        $result = $this->invokeMethod($manager, 'prepareRequestHeader', [$header]);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the executeMultiCurl() method.
     * @covers ::executeMultiCurl
     */
    public function testExecuteMultiCurl()
    {
        /* @var MultiCurl|MockObject $multiCurl */
        $multiCurl = $this->getMockBuilder(MultiCurl::class)
                          ->setMethods(['execute', 'getCurrentExecutionCode'])
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
        /* @var  MultiCurlManager|MockObject $manager */
        $manager = $this->getMockBuilder(MultiCurlManager::class)
                        ->setMethods(['checkStatusMessages'])
                        ->getMock();
        $manager->expects($this->exactly(2))
                ->method('checkStatusMessages');
        $this->injectProperty($manager, 'multiCurl', $multiCurl);

        $result = $this->invokeMethod($manager, 'executeMultiCurl');
        $this->assertEquals($manager, $result);
    }

    /**
     * Tests the checkStatusMessages() method.
     * @covers ::checkStatusMessages
     */
    public function testCheckStatusMessages()
    {
        $statusCode = 42;
        $curlHandle = 'abc';
        $info = ['result' => $statusCode, 'handle' => $curlHandle];

        /* @var MultiCurl|MockObject $multiCurl */
        $multiCurl = $this->getMockBuilder(MultiCurl::class)
                          ->setMethods(['readInfo'])
                          ->getMock();
        $multiCurl->expects($this->at(0))
                  ->method('readInfo')
                  ->willReturn($info);
        $multiCurl->expects($this->at(1))
                  ->method('readInfo')
                  ->willReturn(false);

        /* @var MultiCurlManager|MockObject $manager */
        $manager = $this->getMockBuilder(MultiCurlManager::class)
                        ->setMethods(['processCurlResponse', 'executeNextWaitingRequest'])
                        ->getMock();
        $manager->expects($this->once())
                ->method('processCurlResponse')
                ->with($statusCode, $curlHandle)
                ->willReturnSelf();
        $manager->expects($this->once())
                ->method('executeNextWaitingRequest');
        $this->injectProperty($manager, 'multiCurl', $multiCurl);

        $result = $this->invokeMethod($manager, 'checkStatusMessages');
        $this->assertEquals($manager, $result);
    }

    /**
     * Provides the data for the processCurlResponse() test.
     * @return array The data.
     */
    public function provideProcessCurlResponse(): array
    {
        $request1 = $this->createRequest('abc');
        $request2 = $this->createRequest('def');

        $curlHandle = 'ghi';
        $request = $this->createRequest('jkl');

        /* @var Curl|MockObject $curl */
        $curl = $this->getMockBuilder(Curl::class)
                     ->setMethods(['getHandle'])
                     ->getMock();
        $curl->expects($this->any())
             ->method('getHandle')
             ->willReturn($curlHandle);
        $this->injectProperty($request, 'curl', $curl);

        return [
            [[$request1, $request2], $request, $curlHandle, false, [$request1, $request2]],
            [[$request1, $request, $request2], $request, $curlHandle, true, [0 => $request1, 2 => $request2]],
        ];
    }

    /**
     * Tests the processCurlResponse() method.
     * @param array $runningRequests
     * @param Request $request
     * @param resource $curlHandle
     * @param bool $expectMethodCalls
     * @param array $expectedRunningRequests
     * @covers ::processCurlResponse
     * @dataProvider provideProcessCurlResponse
     */
    public function testProcessCurlResponse(
        array $runningRequests,
        Request $request,
        $curlHandle,
        bool $expectMethodCalls,
        array $expectedRunningRequests
    )
    {
        $statusCode = 42;
        $callback = 'time';
        $request->setOnCompleteCallback($callback);

        /* @var MultiCurl|MockObject $multiCurl */
        $multiCurl = $this->getMockBuilder(MultiCurl::class)
                          ->setMethods(['removeCurl'])
                          ->getMock();
        $multiCurl->expects($expectMethodCalls ? $this->once() : $this->never())
                  ->method('removeCurl')
                  ->with($request->getCurl());

        /* @var MultiCurlManager|MockObject $manager */
        $manager = $this->getMockBuilder(MultiCurlManager::class)
                        ->setMethods(['parseResponse', 'triggerCallback'])
                        ->getMock();
        $manager->expects($expectMethodCalls ? $this->once() : $this->never())
                ->method('parseResponse')
                ->with($request, $statusCode)
                ->willReturnSelf();
        $manager->expects($expectMethodCalls ? $this->once() : $this->never())
                ->method('triggerCallback')
                ->with($callback, $request);
        $this->injectProperty($manager, 'multiCurl', $multiCurl)
             ->injectProperty($manager, 'runningRequests', $runningRequests);

        $result = $this->invokeMethod($manager, 'processCurlResponse', [$statusCode, $curlHandle]);
        $this->assertEquals($manager, $result);
        $this->assertPropertyEquals($expectedRunningRequests, $manager, 'runningRequests');
    }

    /**
     * Provides the data for the parseResponse() test.
     * @return array The data.
     */
    public function provideParseResponse(): array
    {
        return [
            [CURLE_OK, true],
            [CURLE_READ_ERROR, false]
        ];
    }

    /**
     * Tests the parseResponse() method.
     * @param int $errorCode
     * @param bool $expectHydrateCall
     * @covers ::parseResponse
     * @dataProvider provideParseResponse
     */
    public function testParseResponse(int $errorCode, bool $expectHydrateCall)
    {
        $errorMessage = 'abc';

        /* @var Curl|MockObject $curl */
        $curl = $this->getMockBuilder(Curl::class)
                     ->setMethods(['getErrorMessage'])
                     ->getMock();
        $curl->expects($this->once())
             ->method('getErrorMessage')
             ->willReturn($errorMessage);

        /* @var Response|MockObject $response */
        $response = $this->getMockBuilder(Response::class)
                         ->setMethods(['setErrorCode', 'setErrorMessage'])
                         ->getMock();
        $response->expects($this->once())
                 ->method('setErrorCode')
                 ->with($errorCode)
                 ->willReturnSelf();
        $response->expects($this->once())
                 ->method('setErrorMessage')
                 ->with($errorMessage);

        $request = $this->createRequest('def');
        $this->injectProperty($request, 'curl', $curl)
             ->injectProperty($request, 'response', $response);

        /* @var MultiCurlManager|MockObject $manager */
        $manager = $this->getMockBuilder(MultiCurlManager::class)
                        ->setMethods(['hydrateResponseFromCurl'])
                        ->getMock();
        $manager->expects($expectHydrateCall ? $this->once() : $this->never())
                ->method('hydrateResponseFromCurl')
                ->with($response, $curl);

        $result = $this->invokeMethod($manager, 'parseResponse', [$request, $errorCode]);
        $this->assertEquals($manager, $result);
    }

    /**
     * Tests the hydrateResponseFromCurl() method.
     * @covers ::hydrateResponseFromCurl
     */
    public function testHydrateResponseFromCurl()
    {
        $rawContent = 'HeaderContent';
        $headerString = 'Header';
        $content = 'Content';
        $headerSize = 6;
        $statusCode = 200;

        /* @var Curl|MockObject $curl */
        $curl = $this->getMockBuilder(Curl::class)
                     ->setMethods(['getInfo'])
                     ->getMock();
        $curl->expects($this->at(0))
             ->method('getInfo')
             ->with(CURLINFO_HEADER_SIZE)
             ->willReturn($headerSize);
        $curl->expects($this->at(1))
             ->method('getInfo')
             ->with(CURLINFO_HTTP_CODE)
             ->willReturn($statusCode);

        /* @var MultiCurl|MockObject $multiCurl */
        $multiCurl = $this->getMockBuilder(MultiCurl::class)
                          ->setMethods(['getContent'])
                          ->getMock();
        $multiCurl->expects($this->once())
                  ->method('getContent')
                  ->with($curl)
                  ->willReturn($rawContent);

        /* @var Response|MockObject $response */
        $response = $this->getMockBuilder(Response::class)
                         ->setMethods(['setStatusCode', 'setContent'])
                         ->getMock();
        $response->expects($this->once())
                 ->method('setStatusCode')
                 ->with($statusCode)
                 ->willReturnSelf();
        $response->expects($this->once())
                 ->method('setContent')
                 ->with($content);

        /* @var MultiCurlManager|MockObject $manager */
        $manager = $this->getMockBuilder(MultiCurlManager::class)
                        ->setMethods(['parseResponseHeaders'])
                        ->getMock();
        $manager->expects($this->once())
                ->method('parseResponseHeaders')
                ->with($response, $headerString);
        $this->injectProperty($manager, 'multiCurl', $multiCurl);

        $result = $this->invokeMethod($manager, 'hydrateResponseFromCurl', [$response, $curl]);
        $this->assertEquals($manager, $result);
    }

    /**
     * Tests the parseResponseHeaders() method.
     * @covers ::parseResponseHeaders
     */
    public function testParseResponseHeaders()
    {
        $headerString = "HTTP/1.1 301 FOUND\r\nLocation: http://www.example.com/\r\n\r\n"
            . "HTTP/1.1 200 OK\r\nContent-Type: Foo\r\nabc:def\r\n\r\n";

        $expectedHeader1 = new Header();
        $expectedHeader1->set('Location', 'http://www.example.com/');
        $expectedHeader2 = new Header();
        $expectedHeader2->set('Content-Type', 'Foo')
                        ->set('abc', 'def');

        /* @var Response|MockObject $response */
        $response = $this->getMockBuilder(Response::class)
                         ->setMethods(['addHeader'])
                         ->getMock();
        $response->expects($this->at(0))
                 ->method('addHeader')
                 ->with($expectedHeader1);
        $response->expects($this->at(1))
                 ->method('addHeader')
                 ->with($expectedHeader2);

        $manager = new MultiCurlManager();
        $result = $this->invokeMethod($manager, 'parseResponseHeaders', [$response, $headerString]);
        $this->assertEquals($manager, $result);
    }

    /**
     * Tests the triggerCallback() method.
     * @covers ::triggerCallback
     */
    public function testTriggerCallback()
    {
        $expectedRequest = $this->createRequest('abc');
        $callbackResult = false;
        $callback = function(Request $request) use (&$callbackResult, $expectedRequest) {
            $callbackResult = true;
            $this->assertEquals($expectedRequest, $request);
        };

        $manager = new MultiCurlManager();
        $result = $this->invokeMethod($manager, 'triggerCallback', [$callback, $expectedRequest]);
        $this->assertEquals($manager, $result);
        $this->assertTrue($callbackResult);
    }

    /**
     * Provides the data for the waitForAllRequests() test.
     * @return array The data.
     */
    public function provideWaitForAllRequests(): array
    {
        return [
            [[
                    ['getStillRunningRequests', 0]
            ], 0],
            [[
                ['getStillRunningRequests', 42],
                ['getCurrentExecutionCode', CURLM_BAD_HANDLE]
            ], 0],
            [[
                ['getStillRunningRequests', 42],
                ['getCurrentExecutionCode', CURLM_OK],
                ['select', -1],
                ['getStillRunningRequests', 0],
            ], 1],
            [[
                ['getStillRunningRequests', 42],
                ['getCurrentExecutionCode', CURLM_OK],
                ['select', 1337],
                ['getStillRunningRequests', 0],
            ], 1],
            [[
                ['getStillRunningRequests', 42],
                ['getCurrentExecutionCode', CURLM_OK],
                ['select', 1337],
                ['getStillRunningRequests', 42],
                ['getCurrentExecutionCode', CURLM_OK],
                ['select', 1337],
                ['getStillRunningRequests', 0],
            ], 2]
        ];
    }
    /**
     * Tests the waitForAllRequests() method.
     * @param array $multiCurlInvocations The invocations of the MultiCurl wrapper.
     * @param int $expectedExecuteCount The number of times to expect start() to be called.
     * @covers ::waitForAllRequests
     * @dataProvider provideWaitForAllRequests
     */
    public function testWaitForAllRequests(array $multiCurlInvocations, int $expectedExecuteCount)
    {
        /* @var MultiCurl|MockObject $multiCurl */
        $multiCurl = $this->getMockBuilder(MultiCurl::class)
                          ->setMethods(['getStillRunningRequests', 'getCurrentExecutionCode', 'select'])
                          ->getMock();
        foreach ($multiCurlInvocations as $index => $invocation) {
            $multiCurl->expects($this->at($index))
                      ->method(array_shift($invocation))
                      ->willReturn(array_shift($invocation));
        }
        /* @var MultiCurlManager|MockObject $manager */
        $manager = $this->getMockBuilder(MultiCurlManager::class)
                        ->setMethods(array('executeMultiCurl'))
                        ->getMock();
        $manager->expects($this->exactly($expectedExecuteCount))
                ->method('executeMultiCurl');
        $this->injectProperty($manager, 'multiCurl', $multiCurl);
        
        $result = $manager->waitForAllRequests();
        $this->assertEquals($manager, $result);
    }

    /**
     * Provides the data for the waitForSingleRequest() test.
     * @return array The data.
     */
    public function provideWaitForSingleRequest(): array
    {
        return [
            [[
                ['getStillRunningRequests', 0]
            ], [], 0],
            [[
                ['getStillRunningRequests', 1],
                ['getCurrentExecutionCode', CURLM_INTERNAL_ERROR]
            ], [], 0],
            [[
                ['getStillRunningRequests', 1],
                ['getCurrentExecutionCode', CURLM_OK]
            ], [false], 0],

            [[
                ['getStillRunningRequests', 1],
                ['getCurrentExecutionCode', CURLM_OK],
                ['select', 0],
                ['getStillRunningRequests', 0],
            ], [true], 1],
            [[
                ['getStillRunningRequests', 1],
                ['getCurrentExecutionCode', CURLM_OK],
                ['select', 0],
                ['getStillRunningRequests', 1],
                ['getCurrentExecutionCode', CURLM_INTERNAL_ERROR]
            ], [true], 1],
            [[
                ['getStillRunningRequests', 1],
                ['getCurrentExecutionCode', CURLM_OK],
                ['select', 0],
                ['getStillRunningRequests', 1],
                ['getCurrentExecutionCode', CURLM_OK]
            ], [true, false], 1],

            [[
                ['getStillRunningRequests', 1],
                ['getCurrentExecutionCode', CURLM_OK],
                ['select', 0],
                ['getStillRunningRequests', 1],
                ['getCurrentExecutionCode', CURLM_OK],
                ['select', 0],
                ['getStillRunningRequests', 0],
            ], [true, true], 2]
        ];
    }

    /**
     * Tests the waitForSingleRequest() method.
     * @param array $multiCurlInvocations
     * @param array $resultInArray
     * @param int $expectedNumberOfExecuteCalls
     * @covers ::waitForSingleRequest
     * @dataProvider provideWaitForSingleRequest
     */
    public function testWaitForSingleRequest(
        array $multiCurlInvocations,
        array $resultInArray,
        int $expectedNumberOfExecuteCalls
    )
    {
        $request = $this->createRequest('abc');
        $runningRequests = [$this->createRequest('def'), $this->createRequest('ghi')];

        /* @var MultiCurl|MockObject $multiCurl */
        $multiCurl = $this->getMockBuilder(MultiCurl::class)
                          ->setMethods(['getStillRunningRequests', 'getCurrentExecutionCode', 'select'])
                          ->getMock();
        foreach ($multiCurlInvocations as $index => $invocation) {
            $multiCurl->expects($this->at($index))
                      ->method(array_shift($invocation))
                      ->willReturn(array_shift($invocation));
        }

        $functionMocker = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl')
                               ->setFunctions(['in_array'])
                               ->getMock();
        if (empty($resultInArray)) {
            $functionMocker->expects($this->never())
                           ->method('in_array');
        } else {
            foreach ($resultInArray as $index => $result) {
                $functionMocker->expects($this->at($index))
                               ->method('in_array')
                               ->with($request, $runningRequests)
                               ->willReturn($result);
            }
        }

        /* @var MultiCurlManager|MockObject $manager */
        $manager = $this->getMockBuilder(MultiCurlManager::class)
                        ->setMethods(['executeRequest', 'executeMultiCurl'])
                        ->getMock();
        $manager->expects($this->once())
                ->method('executeRequest')
                ->with($request);
        $manager->expects($this->exactly($expectedNumberOfExecuteCalls))
                ->method('executeMultiCurl');
        $this->injectProperty($manager, 'runningRequests', $runningRequests)
             ->injectProperty($manager, 'multiCurl', $multiCurl);

        $result = $manager->waitForSingleRequest($request);
        $this->assertEquals($manager, $result);
    }
}

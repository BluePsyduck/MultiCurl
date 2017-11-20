<?php

namespace BluePsyduckTests\MultiCurl\Wrapper;

use BluePsyduck\MultiCurl\Wrapper\Curl;
use BluePsyduck\MultiCurl\Wrapper\MultiCurl;
use BluePsyduckTestAssets\MultiCurl\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * PHPUnit test of the MultiCurl wrapper.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 *
 * @coversDefaultClass \BluePsyduck\MultiCurl\Wrapper\MultiCurl
 */
class MultiCurlTest extends TestCase
{
    /**
     * Returns the mocked wrapper.
     * @param mixed $handle The handle to set.
     * @return MultiCurl|MockObject
     */
    protected function getMockedWrapper($handle = null)
    {
        $wrapper = $this->getMockBuilder(MultiCurl::class)
                        ->setMethods(['__construct', '__destruct'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->injectProperty($wrapper, 'handle', $handle);
        return $wrapper;
    }

    /**
     * Creates a mocked cUrl wrapper.
     * @param mixed $handle The handle to be returned by the getHandle() method.
     * @return Curl|MockObject
     */
    protected function getMockedCurl($handle)
    {
        $wrapper = $this->getMockBuilder(Curl::class)
                        ->setMethods(['__construct', '__destruct', 'getHandle'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $wrapper->expects($this->any())
                ->method('getHandle')
                ->willReturn($handle);

        return $wrapper;
    }

    /**
     * Tests the __construct() method.
     * @covers ::__construct
     * @runInSeparateProcess
     */
    public function testConstruct()
    {
        $handle = 'abc';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_multi_init'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_multi_init')
                  ->willReturn($handle);

        /* @var MultiCurl|MockObject $wrapper */
        $wrapper = $this->getMockBuilder(MultiCurl::class)
                        ->setMethods(['__destruct'])
                        ->getMock();
        $this->assertPropertyEquals($handle, $wrapper, 'handle');
    }

    /**
     * Tests the __destruct() method.
     * @covers ::__destruct
     * @runInSeparateProcess
     */
    public function testDestruct()
    {
        $handle = 'abc';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_multi_close'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_multi_close')
                  ->with($handle);

        /* @var MultiCurl|MockObject $wrapper */
        $wrapper = $this->getMockBuilder(MultiCurl::class)
                        ->setMethods(['__construct'])
                        ->getMock();
        $this->injectProperty($wrapper, 'handle', $handle);

        $wrapper->__destruct();
    }

    /**
     * Tests the addCurl() method.
     * @covers ::addCurl
     */
    public function testAddCurl()
    {
        $handle = 'abc';
        $curlHandle = 'def';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_multi_add_handle'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_multi_add_handle')
                  ->with($handle, $curlHandle);

        $wrapper = $this->getMockedWrapper($handle);
        $result = $wrapper->addCurl($this->getMockedCurl($curlHandle));
        $this->assertEquals($wrapper, $result);
    }

    /**
     * Tests the removeCurl() method.
     * @covers ::removeCurl
     */
    public function testRemoveCurl()
    {
        $handle = 'abc';
        $curlHandle = 'def';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_multi_remove_handle'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_multi_remove_handle')
                  ->with($handle, $curlHandle);

        $wrapper = $this->getMockedWrapper($handle);
        $result = $wrapper->removeCurl($this->getMockedCurl($curlHandle));
        $this->assertEquals($wrapper, $result);
    }

    /**
     * Tests the getContent() method.
     * @covers ::getContent
     */
    public function testGetContent()
    {
        $curlHandle = 'abc';
        $content = 'def';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_multi_getcontent'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_multi_getcontent')
                  ->with($curlHandle)
                  ->willReturn($content);

        $wrapper = $this->getMockedWrapper();
        $result = $wrapper->getContent($this->getMockedCurl($curlHandle));
        $this->assertEquals($content, $result);
    }

    /**
     * Tests the execute() method.
     * @covers ::execute
     */
    public function testExecute()
    {
        $handle = 'abc';
        $runningRequests = 42;
        $resultExec = 1337;

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(array('curl_multi_exec'))
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_multi_exec')
                  ->with($handle, $runningRequests)
                  ->willReturn($resultExec);

        $wrapper = $this->getMockedWrapper($handle);
        $this->injectProperty($wrapper, 'stillRunningRequests', $runningRequests);
        $result = $wrapper->execute();
        $this->assertEquals($wrapper, $result);
        $this->assertPropertyEquals($resultExec, $wrapper, 'currentExecutionCode');
    }

    /**
     * Tests the readInfo() method.
     * @covers ::readInfo
     */
    public function testReadInfo()
    {
        $handle = 'abc';
        $info = array('result' => 42);

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_multi_info_read'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_multi_info_read')
                  ->with($handle)
                  ->willReturn($info);

        $wrapper = $this->getMockedWrapper($handle);
        $result = $wrapper->readInfo();
        $this->assertEquals($info, $result);
    }

    /**
     * Tests the select() method.
     * @covers ::select
     */
    public function testSelect()
    {
        $handle = 'abc';
        $timeout = 1337;
        $resultSelect = 42;

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_multi_select'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_multi_select')
                  ->with($handle, $timeout)
                  ->willReturn($resultSelect);

        $wrapper = $this->getMockedWrapper($handle);
        $result = $wrapper->select($timeout);
        $this->assertEquals($resultSelect, $result);
    }

    /**
     * Tests the getCurrentExecutionCode() method.
     * @covers ::getCurrentExecutionCode
     */
    public function testGetCurrentExecutionCode()
    {
        $expected = 42;
        $wrapper = $this->getMockedWrapper();
        $this->injectProperty($wrapper, 'currentExecutionCode', $expected);
        $result = $wrapper->getCurrentExecutionCode();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the getStillRunningRequests() method.
     * @covers ::getStillRunningRequests
     */
    public function testGetStillRunningRequests()
    {
        $expected = 42;
        $wrapper = $this->getMockedWrapper();
        $this->injectProperty($wrapper, 'stillRunningRequests', $expected);
        $result = $wrapper->getStillRunningRequests();
        $this->assertEquals($expected, $result);
    }
}

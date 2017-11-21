<?php

namespace BluePsyduckTests\MultiCurl\Wrapper;

use BluePsyduck\MultiCurl\Wrapper\Curl;
use BluePsyduckTestAssets\MultiCurl\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * PHPUnit test of the cUrl wrapper.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 *
 * @coversDefaultClass \BluePsyduck\MultiCurl\Wrapper\Curl
 */
class CurlTest extends TestCase
{
    /**
     * Returns the mocked wrapper.
     * @param resource $handle The handle to set.
     * @return Curl|MockObject
     */
    protected function getMockedWrapper($handle = null)
    {
        $wrapper = $this->getMockBuilder(Curl::class)
                        ->setMethods(['__construct', '__destruct'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->injectProperty($wrapper, 'handle', $handle);
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
                          ->setFunctions(['curl_init'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_init')
                  ->willReturn($handle);

        /* @var Curl|MockObject $wrapper */
        $wrapper = $this->getMockBuilder(Curl::class)
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
                          ->setFunctions(['curl_close'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_close')
                  ->with($handle);

        /* @var Curl|MockObject $wrapper */
        $wrapper = $this->getMockBuilder(Curl::class)
                        ->setMethods(['__construct'])
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->injectProperty($wrapper, 'handle', $handle);

        $wrapper->__destruct();
    }

    /**
     * Tests the getHandle() method.
     * @covers ::getHandle
     */
    public function testGetHandle()
    {
        $expected = 'abc';
        $wrapper = $this->getMockedWrapper();
        $this->injectProperty($wrapper, 'handle', $expected);
        $result = $wrapper->getHandle();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the setOption() method.
     * @covers ::setOption
     */
    public function testSetOption()
    {
        $handle = 'abc';
        $name = 'def';
        $value = 'ghi';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_setopt'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_setopt')
                  ->with($handle, $name, $value);

        $wrapper = $this->getMockedWrapper($handle);
        $result = $wrapper->setOption($name, $value);
        $this->assertEquals($wrapper, $result);
    }

    /**
     * Tests the execute() method.
     * @covers ::execute
     */
    public function testExecute()
    {
        $handle = 'abc';
        $resultExec = 'def';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_exec'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_exec')
                  ->with($handle)
                  ->willReturn($resultExec);

        $wrapper = $this->getMockedWrapper($handle);
        $result = $wrapper->execute();
        $this->assertEquals($resultExec, $result);
    }

    /**
     * Provides the data for the getInfo() test.
     * @return array The data.
     */
    public function provideGetInfo(): array
    {
        return [
            [42, 'ghi'],
            [null, ['def' => 'ghi']]
        ];
    }

    /**
     * Tests the getInfo() method.
     * @param int|null $code
     * @param mixed $expectedResult
     * @covers ::getInfo
     * @dataProvider provideGetInfo
     */
    public function testGetInfo($code, $expectedResult)
    {
        $handle = 'abc';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_getinfo'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_getinfo')
                  ->with($handle, $code)
                  ->willReturn($expectedResult);

        $wrapper = $this->getMockedWrapper($handle);
        $result = $wrapper->getInfo($code);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getErrorCode() method.
     * @covers ::getErrorCode
     */
    public function testGetErrorCode()
    {
        $handle = 'abc';
        $errorCode = 42;

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_errno'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_errno')
                  ->with($handle)
                  ->willReturn($errorCode);

        $wrapper = $this->getMockedWrapper($handle);
        $result = $wrapper->getErrorCode();
        $this->assertEquals($errorCode, $result);
    }

    /**
     * Tests the getErrorMessage() method.
     * @covers ::getErrorMessage
     */
    public function testGetErrorMessage()
    {
        $handle = 'abc';
        $errorMessage = 'def';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(['curl_error'])
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_error')
                  ->with($handle)
                  ->willReturn($errorMessage);

        $wrapper = $this->getMockedWrapper($handle);
        $result = $wrapper->getErrorMessage();
        $this->assertEquals($errorMessage, $result);
    }
}

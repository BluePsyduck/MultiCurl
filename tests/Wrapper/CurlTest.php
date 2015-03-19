<?php

namespace BluePsyduckTests\MultiCurl\Wrapper;

use BluePsyduckTests\MultiCurl\Assets\TestCase;

/**
 * PHPUnit test of the cUrl wrapper.
 *
 * @author Marcel <marcel@mania-community.de>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */
class CurlTest extends TestCase {
    /**
     * Returns the mocked wrapper.
     * @param mixed $handle The handle to set.
     * @return \BluePsyduck\MultiCurl\Wrapper\Curl
     */
    protected function getMockedWrapper($handle = null) {
        $wrapper = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\Curl')
                        ->setMethods(array('__construct', '__destruct'))
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->injectProperty($wrapper, 'handle', $handle);
        return $wrapper;
    }

    /**
     * Tests the __construct() method.
     * @covers \BluePsyduck\MultiCurl\Wrapper\Curl::__construct
     */
    public function testConstruct() {
        $handle = 'abc';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(array('curl_init'))
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_init')
                  ->willReturn($handle);

        /* @var \BluePsyduck\MultiCurl\Wrapper\Curl|\PHPUnit_Framework_MockObject_MockObject $wrapper */
        $wrapper = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\Curl')
                        ->setMethods(array('__destruct'))
                        ->getMock();
        $this->assertPropertyEquals($handle, $wrapper, 'handle');
    }

    /**
     * Tests the __destruct() method.
     * @covers \BluePsyduck\MultiCurl\Wrapper\Curl::__destruct
     */
    public function testDestruct() {
        $handle = 'abc';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(array('curl_close'))
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_close')
                  ->with($handle);

        /* @var \BluePsyduck\MultiCurl\Wrapper\Curl|\PHPUnit_Framework_MockObject_MockObject $wrapper */
        $wrapper = $this->getMockBuilder('BluePsyduck\MultiCurl\Wrapper\Curl')
                        ->setMethods(array('__construct'))
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->injectProperty($wrapper, 'handle', $handle);

        $wrapper->__destruct();
    }

    /**
     * Tests the getHandle() method.
     * @covers \BluePsyduck\MultiCurl\Wrapper\Curl::getHandle
     */
    public function testGetHandle() {
        $expected = 'abc';
        $wrapper = $this->getMockedWrapper();
        $this->injectProperty($wrapper, 'handle', $expected);
        $result = $wrapper->getHandle();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the setOption() method.
     * @covers \BluePsyduck\MultiCurl\Wrapper\Curl::setOption
     */
    public function testSetOption() {
        $handle = 'abc';
        $name = 'def';
        $value = 'ghi';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(array('curl_setopt'))
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
     * @covers \BluePsyduck\MultiCurl\Wrapper\Curl::execute
     */
    public function testExecute() {
        $handle = 'abc';
        $resultExec = 'def';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(array('curl_exec'))
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
     * Tests the getInfo() method.
     * @covers \BluePsyduck\MultiCurl\Wrapper\Curl::getInfo
     */
    public function testGetInfo() {
        $handle = 'abc';
        $name = 'def';
        $info = 'ghi';

        $functions = $this->getFunctionMockBuilder('BluePsyduck\MultiCurl\Wrapper')
                          ->setFunctions(array('curl_getinfo'))
                          ->getMock();
        $functions->expects($this->once())
                  ->method('curl_getinfo')
                  ->with($handle, $name)
                  ->willReturn($info);

        $wrapper = $this->getMockedWrapper($handle);
        $result = $wrapper->getInfo($name);
        $this->assertEquals($info, $result);
    }
}

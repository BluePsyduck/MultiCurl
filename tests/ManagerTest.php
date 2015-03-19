<?php

namespace BluePsyduckTests\MultiCurl;

use BluePsyduck\MultiCurl\Entity\Request;
use BluePsyduck\MultiCurl\Manager;
use BluePsyduckTests\MultiCurl\Assets\TestCase;

/**
 * PHPUnit test of the MultiCurl manager.
 *
 * @author Marcel <marcel@mania-community.de>
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
        /* @var $manager \BluePsyduck\MultiCurl\Manager|\PHPUnit_Framework_MockObject_MockObject */
        $manager = $this->getMockBuilder('BluePsyduck\MultiCurl\Manager')
                        ->setMethods(array('initializeMultiCurl', 'start'))
                        ->getMock();
        $manager->expects($this->at(0))
                ->method('initializeMultiCurl')
                ->willReturnSelf();
        $manager->expects($this->at(1))
                ->method('start')
                ->willReturnSelf();

        $result = $manager->execute();
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

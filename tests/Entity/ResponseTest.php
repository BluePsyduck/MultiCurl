<?php

namespace BluePsyduckTests\MultiCurl\Entity;

use BluePsyduck\MultiCurl\Entity\Response;
use BluePsyduckTests\MultiCurl\Assets\TestCase;

/**
 * The PHPUnit test of the response entity.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */
class ResponseTest extends TestCase {
    /**
     * Tests the setErrorCode() method.
     * @covers \BluePsyduck\MultiCurl\Entity\Response::setErrorCode
     */
    public function testSetErrorCode() {
        $expected = 42;
        $response = new Response();
        $result = $response->setErrorCode($expected);
        $this->assertEquals($response, $result);
        $this->assertPropertyEquals($expected, $response, 'errorCode');
    }

    /**
     * Tests the getErrorCode() method.
     * @covers \BluePsyduck\MultiCurl\Entity\Response::getErrorCode
     */
    public function testGetErrorCode() {
        $expected = 42;
        $response = new Response();
        $this->injectProperty($response, 'errorCode', $expected);
        $result = $response->getErrorCode();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the setErrorMessage() method.
     * @covers \BluePsyduck\MultiCurl\Entity\Response::setErrorMessage
     */
    public function testSetErrorMessage() {
        $expected = 'abc';
        $response = new Response();
        $result = $response->setErrorMessage($expected);
        $this->assertEquals($response, $result);
        $this->assertPropertyEquals($expected, $response, 'errorMessage');
    }

    /**
     * Tests the getErrorMessage() method.
     * @covers \BluePsyduck\MultiCurl\Entity\Response::getErrorMessage
     */
    public function testGetErrorMessage() {
        $expected = 'abc';
        $response = new Response();
        $this->injectProperty($response, 'errorMessage', $expected);
        $result = $response->getErrorMessage();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the setStatusCode() method.
     * @covers \BluePsyduck\MultiCurl\Entity\Response::setStatusCode
     */
    public function testSetStatusCode() {
        $expected = 42;
        $response = new Response();
        $result = $response->setStatusCode($expected);
        $this->assertEquals($response, $result);
        $this->assertPropertyEquals($expected, $response, 'statusCode');
    }
    
    /**
     * Tests the getStatusCode() method.
     * @covers \BluePsyduck\MultiCurl\Entity\Response::getStatusCode
     */
    public function testGetStatusCode() {
        $expected = 42;
        $response = new Response();
        $this->injectProperty($response, 'statusCode', $expected);
        $result = $response->getStatusCode();
        $this->assertEquals($expected, $result);
    }
    
    /**
     * Tests the setHeaders() method.
     * @covers \BluePsyduck\MultiCurl\Entity\Response::setHeaders
     */
    public function testSetHeaders() {
        $expected = array('abc' => 'def');
        $response = new Response();
        $result = $response->setHeaders($expected);
        $this->assertEquals($response, $result);
        $this->assertPropertyEquals($expected, $response, 'headers');
    }
    
    /**
     * Tests the getHeaders() method.
     * @covers \BluePsyduck\MultiCurl\Entity\Response::getHeaders
     */
    public function testGetHeaders() {
        $expected = array('abc' => 'def');
        $response = new Response();
        $this->injectProperty($response, 'headers', $expected);
        $result = $response->getHeaders();
        $this->assertEquals($expected, $result);
    }
    
    /**
     * Tests the setContent() method.
     * @covers \BluePsyduck\MultiCurl\Entity\Response::setContent
     */
    public function testSetContent() {
        $expected = 'abc';
        $response = new Response();
        $result = $response->setContent($expected);
        $this->assertEquals($response, $result);
        $this->assertPropertyEquals($expected, $response, 'content');
    }
    
    /**
     * Tests the getContent() method.
     * @covers \BluePsyduck\MultiCurl\Entity\Response::getContent
     */
    public function testGetContent() {
        $expected = 'abc';
        $response = new Response();
        $this->injectProperty($response, 'content', $expected);
        $result = $response->getContent();
        $this->assertEquals($expected, $result);
    }
}

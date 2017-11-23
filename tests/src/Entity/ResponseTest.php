<?php

namespace BluePsyduckTests\MultiCurl\Entity;

use BluePsyduck\MultiCurl\Entity\Header;
use BluePsyduck\MultiCurl\Entity\Response;
use BluePsyduckTestAssets\MultiCurl\TestCase;

/**
 * The PHPUnit test of the response entity.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 *
 * @coversDefaultClass \BluePsyduck\MultiCurl\Entity\Response
 */
class ResponseTest extends TestCase
{
    /**
     * Tests the __clone() method.
     * @covers ::__clone
     */
    public function testClone()
    {
        $header = new Header();
        $header->set('abc', 'def');
        $response = new Response();
        $response->addHeader($header);

        $clonedResponse = clone($response);
        $header->set('ghi', 'fail');
        $this->assertEquals('def', $clonedResponse->getLastHeader()->get('abc'));
        $this->assertEquals('', $clonedResponse->getLastHeader()->get('ghi'));
    }

    /**
     * Tests the setErrorCode() method.
     * @covers ::setErrorCode
     */
    public function testSetErrorCode()
    {
        $expected = 42;
        $response = new Response();
        $result = $response->setErrorCode($expected);
        $this->assertEquals($response, $result);
        $this->assertPropertyEquals($expected, $response, 'errorCode');
    }

    /**
     * Tests the getErrorCode() method.
     * @covers ::getErrorCode
     */
    public function testGetErrorCode()
    {
        $expected = 42;
        $response = new Response();
        $this->injectProperty($response, 'errorCode', $expected);
        $result = $response->getErrorCode();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the setErrorMessage() method.
     * @covers ::setErrorMessage
     */
    public function testSetErrorMessage()
    {
        $expected = 'abc';
        $response = new Response();
        $result = $response->setErrorMessage($expected);
        $this->assertEquals($response, $result);
        $this->assertPropertyEquals($expected, $response, 'errorMessage');
    }

    /**
     * Tests the getErrorMessage() method.
     * @covers ::getErrorMessage
     */
    public function testGetErrorMessage()
    {
        $expected = 'abc';
        $response = new Response();
        $this->injectProperty($response, 'errorMessage', $expected);
        $result = $response->getErrorMessage();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the setStatusCode() method.
     * @covers ::setStatusCode
     */
    public function testSetStatusCode()
    {
        $expected = 42;
        $response = new Response();
        $result = $response->setStatusCode($expected);
        $this->assertEquals($response, $result);
        $this->assertPropertyEquals($expected, $response, 'statusCode');
    }

    /**
     * Tests the getStatusCode() method.
     * @covers ::getStatusCode
     */
    public function testGetStatusCode()
    {
        $expected = 42;
        $response = new Response();
        $this->injectProperty($response, 'statusCode', $expected);
        $result = $response->getStatusCode();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the addHeader() method.
     * @covers ::addHeader
     */
    public function testAddHeader() {
        $header1 = new Header();
        $header1->set('abc', 'def');
        $header2 = new Header();
        $header2->set('ghi', 'jkl');
        $headers = [$header1];
        $expectedHeaders = [$header1, $header2];

        $response = new Response();
        $this->injectProperty($response, 'headers', $headers);
        $result = $response->addHeader($header2);
        $this->assertEquals($response, $result);
        $this->assertPropertyEquals($expectedHeaders, $response, 'headers');
    }

    /**
     * Tests the getHeaders() method.
     * @covers ::getHeaders
     */
    public function testGetHeaders() {
        $header1 = new Header();
        $header1->set('abc', 'def');
        $header2 = new Header();
        $header2->set('ghi', 'jkl');
        $headers = [$header1, $header2];

        $response = new Response();
        $this->injectProperty($response, 'headers', $headers);
        $result = $response->getHeaders();
        $this->assertEquals($headers, $result);
    }

    /**
     * Tests the getLastHeader() method.
     * @covers ::getLastHeader
     */
    public function testGetLastHeader()
    {
        $header1 = new Header();
        $header1->set('abc', 'def');
        $header2 = new Header();
        $header2->set('ghi', 'jkl');
        $headers = [$header1, $header2];

        $response = new Response();
        $this->injectProperty($response, 'headers', $headers);
        $result = $response->getLastHeader();
        $this->assertEquals($header1, $result);
    }

    /**
     * Tests the setContent() method.
     * @covers ::setContent
     */
    public function testSetContent()
    {
        $expected = 'abc';
        $response = new Response();
        $result = $response->setContent($expected);
        $this->assertEquals($response, $result);
        $this->assertPropertyEquals($expected, $response, 'content');
    }

    /**
     * Tests the getContent() method.
     * @covers ::getContent
     */
    public function testGetContent()
    {
        $expected = 'abc';
        $response = new Response();
        $this->injectProperty($response, 'content', $expected);
        $result = $response->getContent();
        $this->assertEquals($expected, $result);
    }
}

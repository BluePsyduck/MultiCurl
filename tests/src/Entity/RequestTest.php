<?php

namespace BluePsyduckTests\MultiCurl\Entity;

use BluePsyduck\MultiCurl\Entity\Header;
use BluePsyduck\MultiCurl\Entity\Request;
use BluePsyduck\MultiCurl\Entity\Response;
use BluePsyduck\MultiCurl\Wrapper\Curl;
use BluePsyduckTestAssets\MultiCurl\TestCase;

/**
 * The PHPUnit test of the request entity.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 *
 * @coversDefaultClass \BluePsyduck\MultiCurl\Entity\Request
 */
class RequestTest extends TestCase
{
    /**
     * Tests the __construct() method.
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $request = new Request();

        $this->assertPropertyInstanceOf(Header::class, $request, 'header');
        $this->assertPropertyInstanceOf(Curl::class, $request, 'curl');
        $this->assertPropertyInstanceOf(Response::class, $request, 'response');
    }

    /**
     * Tests the __clone() method.
     * @covers ::__clone
     */
    public function testClone()
    {
        $request = new Request();
        $clonedRequest = clone($request);

        $clonedRequest->getResponse()->setContent('abc');
        $request->getResponse()->setContent('def');
        $request->getHeader()->set('ghi', 'jkl');

        $this->assertNotEquals($request->getCurl()->getHandle(), $clonedRequest->getCurl()->getHandle());
        $this->assertEquals('abc', $clonedRequest->getResponse()->getContent());
        $this->assertEquals('', $clonedRequest->getHeader()->get('ghi'));
    }

    /**
     * Tests the setMethod() method.
     * @covers ::setMethod
     */
    public function testSetMethod()
    {
        $expected = 'ABC';
        $request = new Request();
        $result = $request->setMethod($expected);
        $this->assertEquals($request, $result);
        $this->assertPropertyEquals($expected, $request, 'method');
    }

    /**
     * Tests the getMethod() method.
     * @covers ::getMethod
     */
    public function testGetMethod()
    {
        $expected = 'ABC';
        $request = new Request();
        $this->injectProperty($request, 'method', $expected);
        $result = $request->getMethod();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the setUrl() method.
     * @covers ::setUrl
     */
    public function testSetUrl()
    {
        $expected = 'abc';
        $request = new Request();
        $result = $request->setUrl($expected);
        $this->assertEquals($request, $result);
        $this->assertPropertyEquals($expected, $request, 'url');
    }

    /**
     * Tests the getUrl() method.
     * @covers ::getUrl
     */
    public function testGetUrl()
    {
        $expected = 'abc';
        $request = new Request();
        $this->injectProperty($request, 'url', $expected);
        $result = $request->getUrl();
        $this->assertEquals($expected, $result);
    }

    /**
     * Provides the data for the setRequestData() test.
     * @return array The data.
     */
    public function provideSetRequestData()
    {
        return [
            ['abc=def&ghi=jkl', 'abc=def&ghi=jkl'],
            ['{abc:def,ghi:jkl}', '{abc:def,ghi:jkl}'],
            [['abc' => 'def', 'ghi' => 'jkl'], 'abc=def&ghi=jkl']
        ];
    }

    /**
     * Tests the setRequestData() method.
     * @param string|array $requestData The request data to set.
     * @param string $expectedRequestData The expected request data.
     * @covers ::setRequestData
     * @dataProvider provideSetRequestData
     */
    public function testSetRequestData($requestData, string $expectedRequestData)
    {
        $request = new Request();
        $result = $request->setRequestData($requestData);
        $this->assertEquals($request, $result);
        $this->assertPropertyEquals($expectedRequestData, $request, 'requestData');
    }

    /**
     * Tests the getRequestData() method.
     * @covers \BluePsyduck\MultiCurl\Entity\Request::getRequestData
     */
    public function testGetRequestData()
    {
        $expected = 'abc=def&ghi=jkl';
        $request = new Request();
        $this->injectProperty($request, 'requestData', $expected);
        $result = $request->getRequestData();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the getHeader() method.
     * @covers ::getHeader
     */
    public function testGetHeader()
    {
        $expected = new Header();
        $expected->set('abc', 'def');

        $request = new Request();
        $this->injectProperty($request, 'header', $expected);
        $result = $request->getHeader();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the setTimeout() method.
     * @covers ::setTimeout
     */
    public function testSetTimeout()
    {
        $expected = 42;
        $request = new Request();
        $result = $request->setTimeout($expected);
        $this->assertEquals($request, $result);
        $this->assertPropertyEquals($expected, $request, 'timeout');
    }

    /**
     * Tests the getTimeout() method.
     * @covers ::getTimeout
     */
    public function testGetTimeout()
    {
        $expected = 42;
        $request = new Request();
        $this->injectProperty($request, 'timeout', $expected);
        $result = $request->getTimeout();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the setBasicAuth() method.
     * @covers ::setBasicAuth
     */
    public function testSetBasicAuth()
    {
        $username = 'abc';
        $password = 'def';
        $request = new Request();
        $result = $request->setBasicAuth($username, $password);
        $this->assertEquals($request, $result);
        $this->assertPropertyEquals($username, $request, 'basicAuthUsername');
        $this->assertPropertyEquals($password, $request, 'basicAuthPassword');
    }

    /**
     * Tests the getBasicAuthUsername() method.
     * @covers ::getBasicAuthUsername
     */
    public function testGetBasicAuthUsername()
    {
        $expected = 'abc';
        $request = new Request();
        $this->injectProperty($request, 'basicAuthUsername', $expected);
        $result = $request->getBasicAuthUsername();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the getBasicAuthPassword() method.
     * @covers ::getBasicAuthPassword
     */
    public function testGetBasicAuthPassword()
    {
        $expected = 'abc';
        $request = new Request();
        $this->injectProperty($request, 'basicAuthPassword', $expected);
        $result = $request->getBasicAuthPassword();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the setOnInitializeCallback() method.
     * @covers ::setOnInitializeCallback
     */
    public function testSetOnInitializeCallback()
    {
        $expected = 'time';
        $request = new Request();
        $result = $request->setOnInitializeCallback($expected);
        $this->assertEquals($request, $result);
        $this->assertPropertyEquals($expected, $request, 'onInitializeCallback');
    }

    /**
     * Tests the getOnInitializeCallback() method.
     * @covers ::getOnInitializeCallback
     */
    public function testGetOnInitializeCallback()
    {
        $expected = 'time';
        $request = new Request();
        $this->injectProperty($request, 'onInitializeCallback', $expected);
        $result = $request->getOnInitializeCallback();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the setOnCompleteCallback() method.
     * @covers ::setOnCompleteCallback
     */
    public function testSetOnCompleteCallback()
    {
        $expected = 'time';
        $request = new Request();
        $result = $request->setOnCompleteCallback($expected);
        $this->assertEquals($request, $result);
        $this->assertPropertyEquals($expected, $request, 'onCompleteCallback');
    }

    /**
     * Tests the getOnCompleteCallback() method.
     * @covers ::getOnCompleteCallback
     */
    public function testGetOnCompleteCallback()
    {
        $expected = 'time';
        $request = new Request();
        $this->injectProperty($request, 'onCompleteCallback', $expected);
        $result = $request->getOnCompleteCallback();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the getCurl() method.
     * @covers ::getCurl
     */
    public function testGetCurl()
    {
        $expected = new Curl();
        $request = new Request();
        $this->injectProperty($request, 'curl', $expected);
        $result = $request->getCurl();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the getResponse() method.
     * @covers ::getResponse
     */
    public function testGetResponse()
    {
        $expected = new Response();
        $request = new Request();
        $this->injectProperty($request, 'response', $expected);
        $result = $request->getResponse();
        $this->assertEquals($expected, $result);
    }
}

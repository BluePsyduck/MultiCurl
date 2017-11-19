<?php

namespace BluePsyduckTests\MultiCurl\Entity;

use BluePsyduck\MultiCurl\Entity\Header;
use BluePsyduckTestAssets\MultiCurl\TestCase;

/**
 * The PHPUnit test of the header entity class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 *
 * @coversDefaultClass \BluePsyduck\MultiCurl\Entity\Header
 */
class HeaderTest extends TestCase
{
    /**
     * Provides the data for the set() test.
     * @return array The data.
     */
    public function provideSet() {
        return [
            [['abc' => 'def'], 'ghi', 'jkl', ['abc' => 'def', 'ghi' => 'jkl']],
            [[], 'abc', 'def', ['abc' => 'def']],
            [['abc' => 'def'], 'abc', 'ghi', ['abc' => 'ghi']]
        ];
    }

    /**
     * Tests the set() method.
     * @param array $values
     * @param string $name
     * @param string $value
     * @param array $expectedValues
     * @covers ::set
     * @dataProvider provideSet
     */
    public function testSet(array $values, string $name, string $value, array $expectedValues) {
        $header = new Header();
        $this->injectProperty($header, 'values', $values);

        $result = $header->set($name, $value);
        $this->assertEquals($header, $result);
        $this->assertPropertyEquals($expectedValues, $header, 'values');
    }

    /**
     * Provides the data for the has() test.
     * @return array The data.
     */
    public function provideHas() {
        return [
            [[], 'abc', false],
            [['abc' => 'def', 'ghi' => 'jkl'], 'abc', true],
            [['abc' => 'def', 'ghi' => 'jkl'], 'mno', false]
        ];
    }

    /**
     * Tests the has() method.
     * @param array $values
     * @param string $name
     * @param bool $expectedResult
     * @covers ::has
     * @dataProvider provideHas
     */
    public function testHas(array $values, string $name, bool $expectedResult) {
        $header = new Header();
        $this->injectProperty($header, 'values', $values);

        $result = $header->has($name);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the get() test.
     * @return array The data.
     */
    public function provideGet() {
        return [
            [[], 'abc', ''],
            [['abc' => 'def', 'ghi' => 'jkl'], 'abc', 'def'],
            [['abc' => 'def', 'ghi' => 'jkl'], 'mno', '']
        ];
    }

    /**
     * Tests the get() method.
     * @param array $values
     * @param string $name
     * @param string $expectedResult
     * @covers ::get
     * @dataProvider provideGet
     */
    public function testGet(array $values, string $name, string $expectedResult) {
        $header = new Header();
        $this->injectProperty($header, 'values', $values);

        $result = $header->get($name);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getIterator() method.
     * @covers ::getIterator
     */
    public function testGetIterator() {
        $header = new Header();
        $result = $header->getIterator();
        $this->assertInstanceOf('Traversable', $result);
    }
}

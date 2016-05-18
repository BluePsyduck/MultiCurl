<?php
/**
 * PHPUnit test of the Collection class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */

namespace BluePsyduckTests\MultiCurl\Utils;

use ArrayIterator;
use BluePsyduck\MultiCurl\Utils\Collection;
use BluePsyduckTests\MultiCurl\Assets\TestCase;

class CollectionTest extends TestCase {
    /**
     * Injects the items to the collection using reflections.
     * @param \BluePsyduck\MultiCurl\Utils\Collection $collection The collection instance.
     * @param array $items The items to be injected.
     * @return $this Implementing fluent interface.
     */
    protected function injectItems($collection, $items) {
        $this->injectProperty($collection, 'items', $items);
        return $this;
    }

    /**
     * Extracts the items from the collection.
     * @param \BluePsyduck\MultiCurl\Utils\Collection $collection The collection instance.
     * @return array The extracted items.
     */
    protected function extractItems($collection) {
        return $this->extractProperty($collection, 'items');
    }

    /**
     * Tests the __construct() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::__construct
     */
    public function testConstruct() {
        $items = array('abc' => 'def');
        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('merge'))
                           ->disableOriginalConstructor()
                           ->getMock();
        $collection->expects($this->once())
                   ->method('merge')
                   ->with($items);

        $collection->__construct($items);
        $this->assertPropertyEquals(true, $collection, 'isDirty');
    }

    /**
     * Provides the data for the add() test.
     * @return array The test data.
     */
    public function provideAdd() {
        return array(
            array(array(42), array(), 42),
            array(array(42, 21), array(42), 21),
            array(array(42, 21, 21), array(42, 21), 21)
        );
    }

    /**
     * Tests the add() method.
     * @param array $expectedItems The expected items.
     * @param array $items The items to set.
     * @param int $value The value to use.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::add
     * @dataProvider provideAdd
     */
    public function testAdd($expectedItems, $items, $value) {
        $collection = new Collection();
        $this->injectItems($collection, $items);
        $result = $collection->add($value);
        $this->assertEquals($collection, $result);
        $this->assertEquals($expectedItems, $this->extractItems($collection));
        $this->assertPropertyEquals(true, $collection, 'isDirty');
    }

    /**
     * Provides the data for the set() test.
     * @return array The data.
     */
    public function provideSet() {
        return array(
            array(array('abc' => 'def'), true, false, null, array(), 'abc', 'def'),
            array(array('abc' => 'def', 'ghi' => 'jkl'), true, false, null, array('abc' => 'def'), 'ghi', 'jkl'),
            array(array('abc' => 'ghi'), true, true, 'def', array('abc' => 'def'), 'abc', 'ghi'),
            array(array('abc' => 'def'), false, true, 'def', array('abc' => 'def'), 'abc', 'def'),
        );
    }

    /**
     * Tests the set() method.
     * @param array $expectedItems The expected items.
     * @param bool $expectedIsDirty The expected isDirty flag.
     * @param bool|null $resultHas The result of the has() method call, or null if never called.
     * @param mixed|null $resultGet The result of the get() method call, or null if never called.
     * @param array $items The items to set.
     * @param string $name The name to use.
     * @param mixed $value The value to use.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::set
     * @dataProvider provideSet
     */
    public function testSet($expectedItems, $expectedIsDirty, $resultHas, $resultGet, $items, $name, $value) {
        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('has', 'get'))
                           ->getMock();
        $collection->expects(is_null($resultHas) ? $this->never() : $this->once())
                   ->method('has')
                   ->willReturn($resultHas);
        $collection->expects(is_null($resultGet) ? $this->never() : $this->once())
                   ->method('get')
                   ->with($name)
                   ->willReturn($resultGet);

        $this->injectItems($collection, $items)
             ->injectProperty($collection, 'isDirty', false);

        $result = $collection->set($name, $value);
        $this->assertEquals($collection, $result);
        $this->assertEquals($expectedItems, $this->extractItems($collection));
        $this->assertPropertyEquals($expectedIsDirty, $collection, 'isDirty');
    }

    /**
     * Provides the data for the get() test.
     * @return array The test data.
     */
    public function provideGet() {
        return array(
            array('def', true, array('abc' => 'def', 'ghi' => 'jkl'), 'abc', 'mno'),
            array('mno', false, array('abc' => 'def', 'ghi' => 'jkl'), 'pqr', 'mno')
        );
    }

    /**
     * Tests the get() method.
     * @param int $expectedResult The expected result.
     * @param bool $resultHas The result of the has() method call.
     * @param array $items The items to set.
     * @param string $name The name to use.
     * @param mixed $default The default to use.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::get
     * @dataProvider provideGet
     */
    public function testGet($expectedResult, $resultHas, $items, $name, $default) {
        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('has'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('has')
                   ->with($name)
                   ->willReturn($resultHas);
        $this->injectItems($collection, $items);

        $result = $collection->get($name, $default);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the has() test.
     * @return array The data.
     */
    public function provideHas() {
        return array(
            array(true, array('abc' => 'def', 'ghi' => 'jkl'), 'abc'),
            array(true, array('abc' => null, 'ghi' => 'jkl'), 'abc'),
            array(false, array('abc' => 'def', 'ghi' => 'jkl'), 'mno')
        );
    }

    /**
     * Tests the has() method.
     * @param bool $expectedResult The expected result.
     * @param array $items The items to set.
     * @param string $name The name to use.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::has
     * @dataProvider provideHas
     */
    public function testHas($expectedResult, $items, $name) {
        $collection = new Collection();
        $this->injectItems($collection, $items);
        $result = $collection->has($name);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the remove() test.
     * @return array The test data.
     */
    public function provideRemove() {
        return array(
            array(array('ghi' => 'jkl'), true, array('abc' => 'def', 'ghi' => 'jkl'), 'abc'),
            array(array('abc' => 'def', 'ghi' => 'jkl'), false, array('abc' => 'def', 'ghi' => 'jkl'), 'mno')
        );
    }

    /**
     * Tests the remove() method.
     * @param array $expectedItems The expected items.
     * @param bool $expectedIsDirty The expected isDirty flag.
     * @param array $items The items to set.
     * @param string $name The name to use.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::remove
     * @dataProvider provideRemove
     */
    public function testRemove($expectedItems, $expectedIsDirty, $items, $name) {
        $collection = new Collection();
        $this->injectItems($collection, $items)
             ->injectProperty($collection, 'isDirty', false);

        $result = $collection->remove($name);
        $this->assertEquals($collection, $result);
        $this->assertEquals($expectedItems, $this->extractItems($collection));
        $this->assertPropertyEquals($expectedIsDirty, $collection, 'isDirty');
    }

    /**
     * Tests the extract() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::extract
     */
    public function testExtract() {
        $name = 'abc';
        $default = 'def';
        $expectedResult = 'ghi';

        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('get', 'remove'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('get')
                   ->with($name, $default)
                   ->willReturn($expectedResult);
        $collection->expects($this->once())
                   ->method('remove')
                   ->with($name);

        $result = $collection->extract($name, $default);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the count() test.
     * @return array The test data.
     */
    public function provideCount() {
        return array(
            array(0, array()),
            array(1, array(42)),
            array(2, array(42, 21))
        );
    }

    /**
     * Tests the count() method.
     * @param int $expectedResult The expected result.
     * @param array $items The items to set.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::count
     * @dataProvider provideCount
     */
    public function testCount($expectedResult, $items) {
        $collection = new Collection();
        $this->injectItems($collection, $items);
        $result = $collection->count();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Provides the data for the isEmpty() test.
     * @return array The test data.
     */
    public function provideIsEmpty() {
        return array(
            array(true, array()),
            array(false, array(42)),
            array(false, array(42, 21))
        );
    }

    /**
     * Tests the isEmpty() method.
     * @param boolean $expectedResult The expected result.
     * @param array $items The items.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::isEmpty
     * @dataProvider provideIsEmpty
     */
    public function testIsEmpty($expectedResult, $items) {
        $collection = new Collection();
        $this->injectItems($collection, $items);
        $result = $collection->isEmpty();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the clear() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::clear
     */
    public function testClear() {
        $collection = new Collection();
        $this->injectItems($collection, array('abc', 'def'));
        $result = $collection->clear();
        $this->assertEquals(array(), $this->extractItems($collection));
        $this->assertEquals($collection, $result);
        $this->assertPropertyEquals(true, $collection, 'isDirty');
    }

    /**
     * Tests the push() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::push
     */
    public function testPush() {
        $collection = new Collection();
        $this->injectItems($collection, array('def'));
        $collection->push('abc');
        $this->assertEquals(array('abc', 'def'), $this->extractItems($collection));
        $this->assertPropertyEquals(true, $collection, 'isDirty');
    }

    /**
     * Provides the data for the pop() test.
     * @return array The data.
     */
    public function providePop() {
        return array(
            array('abc', array('def'), true, false, array('abc', 'def')),
            array(null, array(), false, true, array())
        );
    }

    /**
     * Tests the pop() method.
     * @param mixed $expectedResult The expected result.
     * @param array $expectedItems The expected items.
     * @param bool $expectedIsDirty The expected isDirty flag.
     * @param bool $resultEmpty The result of the isEmpty() method call.
     * @param array $items The items to set.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::pop
     * @dataProvider providePop
     */
    public function testPop($expectedResult, $expectedItems, $expectedIsDirty, $resultEmpty, $items) {
        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('isEmpty'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('isEmpty')
                   ->willReturn($resultEmpty);
        $this->injectItems($collection, $items);
        $collection->setIsDirty(false);

        $result = $collection->pop();
        $this->assertEquals($expectedItems, $this->extractItems($collection));
        $this->assertEquals($expectedResult, $result);
        $this->assertPropertyEquals($expectedIsDirty, $collection, 'isDirty');
    }

    /**
     * Provides the data for the top() test.
     * @return array The data.
     */
    public function provideTop() {
        return array(
            array('abc', false, array('abc', 'def')),
            array(null, true, array())
        );
    }

    /**
     * Tests the top() method.
     * @param mixed $expectedResult The expected result.
     * @param bool $resultEmpty The result of the isEmpty() method call.
     * @param array $items The items to set.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::top
     * @dataProvider provideTop
     */
    public function testTop($expectedResult, $resultEmpty, $items) {
        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('isEmpty'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('isEmpty')
                   ->willReturn($resultEmpty);
        $this->injectItems($collection, $items);

        $result = $collection->top();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the enqueue() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::enqueue
     */
    public function testEnqueue() {
        $collection = new Collection();
        $this->injectItems($collection, array('abc'));
        $collection->enqueue('def');
        $this->assertEquals(array('abc', 'def'), $this->extractItems($collection));
        $this->assertPropertyEquals(true, $collection, 'isDirty');
    }

    /**
     * Tests the dequeue() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::dequeue
     */
    public function testDequeue() {
        $expectedResult = 'abc';

        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('pop'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('pop')
                   ->willReturn($expectedResult);

        $result = $collection->dequeue();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getIterator() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::getIterator
     */
    public function testGetIterator() {
        $collection = new Collection();
        $result = $collection->getIterator();
        $this->assertInstanceOf('Traversable', $result);
    }

    /**
     * Tests the toArray() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::toArray
     */
    public function testToArray() {
        $expected = array('abc', 'def');
        $collection = new Collection();
        $this->injectItems($collection, $expected);
        $result = $collection->toArray();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the getKeys() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::getKeys
     */
    public function testGetKeys() {
        $items = array('abc' => 'def', 'ghi' => 'jkl');
        $keys = array('abc', 'ghi');

        $collection = new Collection();
        $this->injectItems($collection, $items);
        $result = $collection->getKeys();
        $this->assertInstanceOf('BluePsyduck\MultiCurl\Utils\Collection', $result);
        $this->assertEquals($keys, $this->extractItems($result));
    }

    /**
     * Tests the getValues() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::getValues
     */
    public function testGetValues() {
        $items = array('abc' => 'def', 'ghi' => 'jkl');
        $values = array('def', 'jkl');

        $collection = new Collection();
        $this->injectItems($collection, $items);
        $result = $collection->getValues();
        $this->assertInstanceOf('BluePsyduck\MultiCurl\Utils\Collection', $result);
        $this->assertEquals($values, $this->extractItems($result));
    }

    /**
     * Provides the data for the merge() test.
     * @return array The data.
     */
    public function provideMerge() {
        return array(
            array(array('abc' => 'def', 'ghi' => 'jkl'), array('abc' => 'def'), array('ghi' => 'jkl')),
            array(
                array('abc' => 'def', 'ghi' => 'jkl'),
                array('abc' => 'def'),
                new ArrayIterator(array('ghi' => 'jkl'))
            )
        );
    }

    /**
     * Tests the __construct() method.
     * @param array $expectedItems The expected items.
     * @param array $items The items to set.
     * @param mixed $itemsToMerge The items to use.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::merge
     * @dataProvider provideMerge
     */
    public function testMerge($expectedItems, $items, $itemsToMerge) {
        $collection = new Collection();
        $this->injectItems($collection, $items);
        $result = $collection->merge($itemsToMerge);
        $this->assertEquals($collection, $result);
        $this->assertEquals($expectedItems, $this->extractItems($collection));
        $this->assertPropertyEquals(true, $collection, 'isDirty');
    }

    /**
     * Tests the reverse() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::reverse
     */
    public function testReverse() {
        $items = array('abc' => 'def', 'ghi' => 'jkl');
        $expectedItems = array('ghi' => 'jkl', 'abc' => 'def');

        $collection = new Collection();
        $this->injectItems($collection, $items);
        $result = $collection->reverse();
        $this->assertEquals($collection, $result);
        $this->assertEquals($expectedItems, $this->extractItems($collection));
    }

    /**
     * Tests the setIsDirty() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::setIsDirty
     */
    public function testSetIsDirty() {
        $expected = true;
        $collection = new Collection();
        $result = $collection->setIsDirty($expected);
        $this->assertEquals($collection, $result);
        $this->assertPropertyEquals($expected, $collection, 'isDirty');
    }

    /**
     * Tests the isDirty() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::isDirty
     */
    public function testIsDirty() {
        $expected = true;
        $collection = new Collection();
        $this->injectProperty($collection, 'isDirty', $expected);
        $result = $collection->isDirty();
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the offsetSet() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::offsetSet
     */
    public function testOffsetSet() {
        $name = 'abc';
        $value = 'def';

        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('set'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('set')
                   ->with($name, $value);
        $collection[$name] = $value;
    }

    /**
     * Tests the offsetGet() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::offsetGet
     */
    public function testOffsetGet() {
        $name = 'abc';
        $value = 'def';

        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('get'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('get')
                   ->with($name)
                   ->willReturn($value);
        $this->assertEquals($value, $collection[$name]);
    }

    /**
     * Tests the offsetExists() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::offsetExists
     */
    public function testOffsetExists() {
        $name = 'abc';
        $result = true;

        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('has'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('has')
                   ->with($name)
                   ->willReturn($result);
        $this->assertEquals($result, isset($collection[$name]));
    }

    /**
     * Tests the offsetUnset() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::offsetUnset
     */
    public function testOffsetUnset() {
        $name = 'abc';

        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('remove'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('remove')
                   ->with($name);
        unset($collection[$name]);
    }


    /**
     * Tests the __set() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::__set
     */
    public function testMagicSet() {
        $name = 'abc';
        $value = 'def';

        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('set'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('set')
                   ->with($name, $value);
        $collection->$name = $value;
    }

    /**
     * Tests the __get() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::__get
     */
    public function testMagicGet() {
        $name = 'abc';
        $value = 'def';

        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('get'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('get')
                   ->with($name)
                   ->willReturn($value);
        $this->assertEquals($value, $collection->$name);
    }

    /**
     * Tests the __isset() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::__isset
     */
    public function testMagicIsset() {
        $name = 'abc';
        $result = true;

        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('has'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('has')
                   ->with($name)
                   ->willReturn($result);
        $this->assertEquals($result, isset($collection->$name));
    }

    /**
     * Tests the __unset() method.
     * @covers \BluePsyduck\MultiCurl\Utils\Collection::__unset
     */
    public function testMagicUnset() {
        $name = 'abc';

        /* @var $collection \BluePsyduck\MultiCurl\Utils\Collection|\PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder('BluePsyduck\MultiCurl\Utils\Collection')
                           ->setMethods(array('remove'))
                           ->getMock();
        $collection->expects($this->once())
                   ->method('remove')
                   ->with($name);
        unset($collection->$name);
    }
}
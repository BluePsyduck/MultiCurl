<?php

namespace BluePsyduckTests\MultiCurl\Assets;

use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Helper class for testing classes with private or protected properties.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */
class TestCase extends PHPUnit_Framework_TestCase {
    /**
     * Asserts that a property of an object equals the an expected value.
     * @param mixed $expected The value to be expected.
     * @param object $object The object.
     * @param string $name The name of the property.
     * @return $this Implementing fluent interface.
     */
    public function assertPropertyEquals($expected, $object, $name) {
        $reflectedProperty = new ReflectionProperty($object, $name);
        $reflectedProperty->setAccessible(true);
        $this->assertEquals($expected, $reflectedProperty->getValue($object));
        return $this;
    }

    /**
     * Asserts that a property is an object of the specified class.
     * @param string $expected The class to be expected.
     * @param object $object The object.
     * @param string $name The name of the property.
     * @return $this Implementing fluent interface.
     */
    public function assertPropertyInstanceOf($expected, $object, $name) {
        $this->assertInstanceOf($expected, $this->extractProperty($object, $name));
        return $this;
    }

    /**
     * Injects a property value to an object.
     * @param object $object The object.
     * @param string $name The name of the property.
     * @param mixed $value The property to be injected.
     * @return $this Implementing fluent interface.
     */
    protected function injectProperty($object, $name, $value) {
        $reflectedProperty = new ReflectionProperty($object, $name);
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($object, $value);
        return $this;
    }

    /**
     * Extracts a property value from an object.
     * @param object $object The object.
     * @param string $name The name of the property.
     * @return mixed The extracted value.
     */
    protected function extractProperty($object, $name) {
        $reflectedProperty = new ReflectionProperty($object, $name);
        $reflectedProperty->setAccessible(true);
        return $reflectedProperty->getValue($object);
    }

    /**
     * Invokes a method of an object.
     * @param object $object The object.
     * @param string $name The name of the method.
     * @param array $params The parameters to be passed to the method.
     * @return mixed The return value of the method.
     */
    protected function invokeMethod($object, $name, $params = array()) {
        $reflectedMethod = new ReflectionMethod($object, $name);
        $reflectedMethod->setAccessible(true);
        return $reflectedMethod->invokeArgs($object, $params);
    }

    /**
     * Creates and returns an function mocker instance.
     * @param string $namespace The namespace to mock the functions into.
     * @return \BluePsyduckTests\MultiCurl\Assets\FunctionMocker
     */
    protected function getFunctionMockBuilder($namespace) {
        $mockBuilder = new FunctionMocker();
        $mockBuilder->setTestCase($this)
                    ->setNamespace($namespace);
        return $mockBuilder;
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public static function tearDownAfterClass() {
        FunctionMocker::resetCurrentMocks();
    }
}
<?php
/**
 * A wrapper class for a collection of items.
 *
 * This class can be used in different modes using the listed methods:
 * - List: add()
 * - Stack (FIFO): push(), pop(), top()
 * - Queue (LIFO): enqueue(), dequeue(), top()
 * - Map: set(), get(), has(), remove()
 * It is also possible to mix unnamed items (list, stack, queue) with named items (map), yet the order when iterating
 * over the collection may be unpredictable. E.g. the pop() and dequeue() methods will return the named items, as if
 * they were unnamed.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */

namespace BluePsyduck\MultiCurl\Utils;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

class Collection implements ArrayAccess, IteratorAggregate, Countable {
    /**
     * The items of the collection.
     * @var array
     */
    protected $items = array();

    /**
     * Whether there has been modifications to the collection.
     * @var bool
     */
    protected $isDirty = true;

    /**
     * Initializes the collection.
     * @param array|\Traversable $items The items to initially set.
     */
    public function __construct($items = array()) {
        $this->merge($items);
    }

    /**
     * Adds an unnamed item to the collection.
     * @param mixed $value The value to be added.
     * @return $this Implementing fluent interface.
     */
    public function add($value) {
        $this->items[] = $value;
        $this->isDirty = true;
        return $this;
    }

    /**
     * Sets a named item to the collection.
     * @param string $name The name of the item.
     * @param mixed $value The value to be added.
     * @return $this Implementing fluent interface.
     */
    public function set($name, $value) {
        if (!$this->has($name) || $this->get($name) !== $value) {
            $this->items[$name] = $value;
            $this->isDirty = true;
        }
        return $this;
    }

    /**
     * Returns a named item from the collection
     * @param string $name The name of the item to be returned.
     * @param mixed $default The value to return if the name is not known.
     * @return mixed The value.
     */
    public function get($name, $default = null) {
        $result = $default;
        if ($this->has($name)) {
            $result = $this->items[$name];
        }
        return $result;
    }

    /**
     * Checks whether the name is known to the collection.
     * @param string $name The name to check.
     * @return bool The result of the check.
     */
    public function has($name) {
        return array_key_exists($name, $this->items);
    }

    /**
     * Removes a named item from the collection.
     * @param string $name The name to be removed.
     * @return $this Implementing fluent interface.
     */
    public function remove($name) {
        if ($this->has($name)) {
            unset($this->items[$name]);
            $this->isDirty = true;
        }
        return $this;
    }

    /**
     * Extracts the value with the specified name from the collection. The extracted value will no longer be part of the
     * collection.
     * @param string $name The name to extract.
     * @param mixed $default The default value if the name is not known.
     * @return mixed The extracted value.
     */
    public function extract($name, $default = null) {
        $result = $this->get($name, $default);
        $this->remove($name);
        return $result;
    }

    /**
     * Counts the items in the collection.
     * @return int The number of items.
     */
    public function count() {
        return count($this->items);
    }

    /**
     * Checks whether the collection is empty.
     * @return bool The result of the check.
     */
    public function isEmpty() {
        return empty($this->items);
    }

    /**
     * Removes all items from the collection.
     * @return $this Implementing fluent interface.
     */
    public function clear() {
        $this->items = array();
        $this->isDirty = true;
        return $this;
    }

    /**
     * Pushes an item into the collection, treating it as a stack.
     * @param mixed $value The value to push.
     * @return $this Implementing fluent interface.
     */
    public function push($value) {
        array_unshift($this->items, $value);
        $this->isDirty = true;
        return $this;
    }

    /**
     * Pops an item from the collection, treating it as a stack.
     * @return mixed|null The popped value, or null if the collection was empty.
     */
    public function pop() {
        $result = null;
        if (!$this->isEmpty()) {
            $result = array_shift($this->items);
            $this->isDirty = true;
        }
        return $result;
    }

    /**
     * Returns the top item from the collection, treating it as stack or queue.
     * @return mixed|null The top value, or null if the collection was empty.
     */
    public function top() {
        $result = null;
        if (!$this->isEmpty()) {
            reset($this->items);
            $result = current($this->items);
        }
        return $result;
    }

    /**
     * Enqueues an item into the collection, treating it as a queue.
     * @param mixed $value The value to enqueue.
     * @return $this Implementing fluent interface.
     */

    public function enqueue($value) {
        array_push($this->items, $value);
        $this->isDirty = true;
        return $this;
    }

    /**
     * Dequeues an item from the collection, treating it as a queue.
     * @return mixed|null The dequeued value, or null if the collection was empty.
     */
    public function dequeue() {
        return $this->pop();
    }

    /**
     * Returns the iterator for the collection.
     * @return \Traversable The iterator.
     */
    public function getIterator() {
        return new ArrayIterator($this->items);
    }

    /**
     * Transforms the collection into an array.
     * @return array The items.
     */
    public function toArray() {
        return $this->items;
    }

    /**
     * Returns the known keys of the collection.
     * @return \BluePsyduck\MultiCurl\Utils\Collection The keys.
     */
    public function getKeys() {
        return new Collection(array_keys($this->items));
    }

    /**
     * Returns the values of the collection, changing the keys to numeric ones.
     * @return \BluePsyduck\MultiCurl\Utils\Collection The values.
     */
    public function getValues() {
        return new Collection(array_values($this->items));
    }

    /**
     * Merges multiple items into the current instance.
     * @param array|\Traversable $items The items to initially set.
     * @return $this Implementing fluent interface.
     */
    public function merge($items) {
        if ($items instanceof Traversable) {
            $this->items = array_merge($this->items, iterator_to_array($items));
        } elseif (is_array($items)) {
            $this->items = array_merge($this->items, $items);
        }
        $this->isDirty = true;
        return $this;
    }

    /**
     * Reverses the order of the items.
     * @return $this Implementing fluent interface.
     */
    public function reverse() {
        $this->items = array_reverse($this->items, true);
        $this->isDirty = true;
        return $this;
    }

    /**
     * Sets whether there has been modifications to the collection.
     * @param bool $isDirty The dirty flag.
     * @return $this Implementing fluent interface.
     */
    public function setIsDirty($isDirty) {
        $this->isDirty = (bool) $isDirty;
        return $this;
    }

    /**
     * Returns whether there has been modifications to the collection.
     * @return bool The dirty flag.
     */
    public function isDirty() {
        return $this->isDirty;
    }

    /**
     * (ArrayAccess) Sets a named value into the collection.
     * @param string $name The name of the item to be set.
     * @param mixed $value The value to be set.
     */
    public function offsetSet($name, $value) {
        $this->set($name, $value);
    }

    /**
     * (ArrayAccess) Returns a named value from the collection.
     * @param string $name The name of the item to be returned.
     * @return mixed The value, or null if the name is not known.
     */
    public function offsetGet($name) {
        return $this->get($name);
    }

    /**
     * (ArrayAccess) Checks if the specified name is known to the collection.
     * @param string $name The name to be checked.
     * @return boolean The result of the check.
     */
    public function offsetExists($name) {
        return $this->has($name);
    }

    /**
     * (ArrayAccess) Removes a named item from the collection.
     * @param string $name The name to be removed.
     */
    public function offsetUnset($name) {
        $this->remove($name);
    }

    /**
     * (Magic Methods) Sets a named value into the collection.
     * @param string $name The name of the value to be set.
     * @param mixed $value The value to be set.
     */
    public function __set($name, $value) {
        $this->set($name, $value);
    }

    /**
     * (Magic Methods) Returns a value from the collection.
     * @param string $name The name of the value to be returned.
     * @return mixed The value, or null if the name is not known.
     */
    public function __get($name) {
        return $this->get($name);
    }

    /**
     * (Magic Methods) Checks if the specified name is known to the cache.
     * @param string $name The name to be checked.
     * @return boolean The result of the check.
     */
    public function __isset($name) {
        return $this->has($name);
    }

    /**
     * (Magic Methods) Removes a named item from the collection.
     * @param string $name The name to be removed.
     */
    public function __unset($name) {
        $this->remove($name);
    }
}
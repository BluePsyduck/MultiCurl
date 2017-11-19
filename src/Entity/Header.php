<?php

namespace BluePsyduck\MultiCurl\Entity;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * The entity class representing the header of a request or response.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-2.0 GPL v2
 */
class Header implements IteratorAggregate
{
    /**
     * The values of the header.
     * @var array|string[]
     */
    protected $values = [];

    /**
     * Sets a value to the header.
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function set(string $name, string $value)
    {
        $this->values[$name] = $value;
        return $this;
    }

    /**
     * Returns whether the specified name is available in the header.
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->values[$name]);
    }

    /**
     * Returns a value from the header.
     * @param string $name
     * @return string
     */
    public function get(string $name): string
    {
        return $this->values[$name] ?? '';
    }

    /**
     * Returns the iterator of the header values.
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values);
    }
}
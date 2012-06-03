<?php

namespace FreeAgent\WorkflowBundle\Step;

class Collection implements \Countable, \IteratorAggregate, \ArrayAccess
{

    protected $collection = array();

    public function __construct(array $collection = array())
    {
        if (!empty($collection)) {
            $this->setCollection($collection);
        }
    }

    public function setCollection(array $collection)
    {
        foreach ($collection as $offset => $value) {
            $this->offsetSet($offset, $value);
        }

    }

    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->collection[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Step) {
            throw new \InvalidArgumentException('Item must be an instance of Step');
        }

        $this->collection[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    public function count()
    {
        return count($this->collection);
    }

    public function getInitialCount()
    {
        return $this->initialCount;
    }

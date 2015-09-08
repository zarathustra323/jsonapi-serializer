<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure;

use \Iterator;
use \ArrayAccess;
use \Countable;

/**
 * Collection object that contains multiple resource documents.
 * Is used for establishing an API response with multiple resources, such as a findMany or findQuery.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class Collection implements Iterator, ArrayAccess, Countable
{
    /**
     * Resources assign to this collection.
     *
     * @var Collection[]
     */
    protected $resources = [];

    /**
     * The array position.
     *
     * @var int
     */
    protected $pos = 0;

    /**
     * Constructor.
     *
     * @param   string  $type
     */
    public function __construct()
    {
        $this->pos = 0;
    }

    /**
     * Gets a unique list of all entity types assigned to this collection.
     *
     * @return  array
     */
    public function getResourceTypes()
    {
        $types = [];
        foreach ($this as $resource) {
            $types[] = $resource->getType();
        }
        return array_unique($types);
    }

    /**
     * Adds a resource to this collection.
     *
     * @param   Resource    $resource
     * @return  self
     */
    public function add(Resource $resource)
    {
        $this->resources[] = $resource;
        return $this;
    }

    /**
     * Returns all resources in this collection.
     *
     * @return  Resource[]
     */
    public function all()
    {
        return $this->resources;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->resources);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->pos = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->resources[$this->pos];
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->pos;
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        ++$this->pos;
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return isset($this->resources[$this->pos]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->add($value);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->resources[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->resources[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return isset($this->resources[$offset]) ? $this->resources[$offset] : null;
    }
}

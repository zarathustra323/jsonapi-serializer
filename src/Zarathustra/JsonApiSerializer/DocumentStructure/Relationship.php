<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure;

abstract class Relationship
{
    use Traits\MetaEnabled;

    /**
     * The attribute key (field name).
     *
     * @var string
     */
    protected $key;

    /**
     * The relationship data.
     *
     * @var Resource
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param   string  $key
     * @param   mixed   $value
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Gets the relationship key (field) name
     *
     * @return  string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Applys the relationship data.
     *
     * @param   Resource
     * @return  self
     */
    abstract public function applyData(Resource $data);

    /**
     * Gets the relationship data.
     *
     * @return  Resource|Resource[]
     */
    public function getData()
    {
        return $this->data;
    }
}

<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure;

class Relationship
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
     * @var Resource[]
     */
    protected $data = [];

    /**
     * Constructor.
     *
     * @param   string  $key
     * @param   mixed   $value
     */
    public function __construct($key, Resource $data = null)
    {
        $this->key = $key;
        if (null !== $data) {
            $this->pushData($data);
        }
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
    public function pushData(Resource $data)
    {
        $this->data[] = $data;
        return $this;
    }

    /**
     * Gets the relationship data.
     *
     * @return  Resource[]
     */
    public function getData()
    {
        return $this->data;
    }
}

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
     * @var RelatedDataInterface
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param   string                      $key
     * @param   RelatedDataInterface|null   $data
     */
    public function __construct($key, RelatedDataInterface $data = null)
    {
        $this->key = $key;
        if (null !== $data) {
            $this->pushData($data);
        }
    }

    /**
     * Determines if any resource data has been applied.
     *
     * @return  bool
     */
    public function hasData()
    {
        return null !== $this->getData();
    }

    /**
     * Determines if this relationship is a collection of resources.
     *
     * @return  bool
     */
    public function isCollection()
    {
        if (false === $this->hasData()) {
            return false;
        }
        return $this->getData() instanceof ResourceCollection;
    }

    /**
     * Determines if this relationship is a single resource.
     *
     * @return  bool
     */
    public function isResource()
    {
        if (false === $this->hasData()) {
            return false;
        }
        return $this->getData() instanceof Resource;
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
     * @param   RelatedDataInterface
     * @return  self
     */
    public function pushData(RelatedDataInterface $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Gets the relationship data.
     *
     * @return  RelatedDataInterface
     */
    public function getData()
    {
        return $this->data;
    }
}

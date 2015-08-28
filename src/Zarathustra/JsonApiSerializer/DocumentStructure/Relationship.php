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
     * The owning resource of the relationship.
     *
     * @var Resource
     */
    protected $owner;

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
     * Gets the owning resource of this relationship.
     *
     * @return  Resource
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Sets the owning resource of this relationship.
     *
     * @param   Resource    $owner
     * @return  self
     */
    public function setOwner(Resource $owner)
    {
        $this->owner = $owner;
        return $this;
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

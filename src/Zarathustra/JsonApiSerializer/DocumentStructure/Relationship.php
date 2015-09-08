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
     * Whether the relationship is one or many.
     *
     * @var string
     */
    protected $relType;

    /**
     * Constructor.
     *
     * @param   string                      $key
     * @param   RelatedDataInterface|null   $data
     */
    public function __construct($key, $relType)
    {
        $this->key = $key;
        $this->relType = $relType;
        if ($this->isMany()) {
            $this->data = new Collection();
        }
    }

    /**
     * Determines if this is an is-one relationship.
     *
     * @return  bool
     */
    public function isOne()
    {
        return false === $this->isMany();
    }

    /**
     * Determines if this is an is-many relationship.
     *
     * @return  bool
     */
    public function isMany()
    {
        return 'many' === $this->relType;
    }

    /**
     * Determines if any resource data has been applied.
     *
     * @return  bool
     */
    public function hasData()
    {
        if ($this->isOne()) {
            return null !== $this->getData();
        }
        return 0 < count($this->getData());
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
     * @param   Resource    $resource
     * @return  self
     */
    public function pushData(Resource $resource)
    {
        if ($this->isMany()) {
            $this->data[] = $resource;
            return $this;
        }
        $this->data = $resource;
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

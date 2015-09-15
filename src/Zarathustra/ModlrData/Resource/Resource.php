<?php

namespace Zarathustra\ModlrData\Resource;

class Resource
{
    use Traits\MetaEnabled;

    /**
     * The top level, primary entity type for this resource.
     *
     * @var string
     */
    protected $entityType;

    /**
     * The resource type: representing either one or many entites.
     *
     * @var string
     */
    protected $resourceType;

    /**
     * The resources's primary data.
     *
     * @var Entity|Collection|null
     */
    protected $primaryData;

    /**
     * Constructor.
     *
     * @param   string  $entityType
     * @param   string  $resourceType
     */
    public function __construct($entityType, $resourceType = 'one')
    {
        $this->entityType = $entityType;
        $this->resourceType = $resourceType;
        if ($this->isMany()) {
            $this->primaryData = new Collection();
        }
    }

    /**
     * Gets the top level, primary entity type this resource represents.
     *
     * @return  string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Determines if this is an is-one resource.
     *
     * @return  bool
     */
    public function isOne()
    {
        return false === $this->isMany();
    }

    /**
     * Determines if this is an is-many resource.
     *
     * @return  bool
     */
    public function isMany()
    {
        return 'many' === $this->docType;
    }

    /**
     * Pushes entities to this resource.
     *
     * @param   Entity    $entity
     * @return  self
     */
    public function pushData(Entity $entity)
    {
        if ($this->isMany()) {
            $this->primaryData[] = $entity;
            return $this;
        }
        $this->primaryData = $entity;
        return $this;
    }

    /**
     * Gets the primary resource data.
     *
     * @return  Entity|Collection|null
     */
    public function getPrimaryData()
    {
        return $this->primaryData;
    }
}

<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure;

class Document
{
    use Traits\MetaEnabled;

    /**
     * The top level entity type for this document.
     *
     * @var string
     */
    protected $entityType;

    /**
     * The document type: representing either one or many resources.
     *
     * @var string
     */
    protected $docType;

    /**
     * The document's primary data.
     *
     * @var Resource|Collection|null
     */
    protected $primaryData;

    /**
     * Constructor.
     *
     * @param   string  $entityType
     * @param   string  $docType
     */
    public function __construct($entityType, $docType = 'one')
    {
        $this->entityType = $entityType;
        $this->docType = $docType;
        if ($this->isMany()) {
            $this->primaryData = new Collection();
        }
    }

    /**
     * Gets the top level entity type this document represents.
     *
     * @return  string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Determines if this is an is-one document.
     *
     * @return  bool
     */
    public function isOne()
    {
        return false === $this->isMany();
    }

    /**
     * Determines if this is an is-many document.
     *
     * @return  bool
     */
    public function isMany()
    {
        return 'many' === $this->docType;
    }

    /**
     * Pushes resources to the document.
     *
     * @param   Resource    $resource
     * @return  self
     */
    public function pushData(Resource $resource)
    {
        if ($this->isMany()) {
            $this->primaryData[] = $resource;
            return $this;
        }
        $this->primaryData = $resource;
        return $this;
    }

    /**
     * Gets the primary document data.
     *
     * @return  Resource|Collection|null
     */
    public function getPrimaryData()
    {
        return $this->primaryData;
    }
}

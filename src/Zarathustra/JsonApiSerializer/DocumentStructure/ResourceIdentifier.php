<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure;

class ResourceIdentifier
{
    use Traits\MetaEnabled;

    /**
     * The resource id.
     *
     * @var string
     */
    protected $id;

    /**
     * The resource entity type.
     *
     * @var string
     */
    protected $type;

    /**
     * Constructor.
     *
     * @param   string  $id
     * @param   string  $type
     */
    public function __construct($id, $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    /**
     * Gets the resource id.
     *
     * @return  string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the resource entity type.
     *
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }
}

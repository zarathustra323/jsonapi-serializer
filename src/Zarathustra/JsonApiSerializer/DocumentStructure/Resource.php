<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure;

class Resource implements RelatedDataInterface
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
     * Attribute objects assigned to the resource.
     *
     * @var Attribute[]
     */
    protected $attributes = [];

    /**
     * Relationship objects assigned to the resource.
     *
     * @var Relationship[]
     */
    protected $relationships = [];

    /**
     * Constructor.
     *
     * @param   string  $id
     * @param   string  $type
     */
    public function __construct($id, $type)
    {
        $this->id = (String) $id;
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

    /**
     * Gets all attribute objects of this resource.
     *
     * @return  Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Adds an attribute to this resource.
     *
     * @param   Attribute   $attribute
     * @return  self
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributes[$attribute->getKey()] = $attribute;
        return $this;
    }

    /**
     * Gets an attribute from this resource.
     * Returns null if the attribute doesn't exist.
     *
     * @param   string  $key
     * @return  Attribute|null
     */
    public function getAttribute($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
        return null;
    }

    /**
     * Determines if an attribute exists on this resource.
     *
     * @param   string  $key
     * @return  bool
     */
    public function hasAttribute($key)
    {
        return null !== $this->getAttribute($key);
    }

    /**
     * Gets all relationship objects of this resource.
     *
     * @return  Relationship[]
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    /**
     * Adds a relationship to this resource.
     *
     * @param   Relationship   $relationship
     * @return  self
     */
    public function addRelationship(Relationship $relationship)
    {
        $this->relationships[$relationship->getKey()] = $relationship;
        return $this;
    }

    /**
     * Gets a relationship from this resource.
     * Returns null if the relationship doesn't exist.
     *
     * @param   string  $key
     * @return  Relationship|null
     */
    public function getRelationship($key)
    {
        if (isset($this->relationships[$key])) {
            return $this->relationships[$key];
        }
        return null;
    }

    /**
     * Determines if a relationship exists on this resource.
     *
     * @param   string  $key
     * @return  bool
     */
    public function hasRelationship($key)
    {
        return null !== $this->getRelationship($key);
    }
}

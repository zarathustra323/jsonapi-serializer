<?php

namespace Zarathustra\JsonApiSerializer\Metadata;

/**
 * Defines serialization metadata for an entity (e.g. a database object).
 * Should be loaded using the MetadataFactory, not instantiated directly.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class EntityMetadata
{
    /**
     * Uniquely defines the type of entity.
     * The value is used as the "type" field of the JSON API spec's resource identifier object.
     *
     * @var string
     */
    public $type;

    /**
     * All attribute fields assigned to this entity.
     * An attribute is a "standard" field, such as a string, integer, array, etc.
     *
     * @var AttributeMetadata[]
     */
    public $attributes = [];

    /**
     * All relationship fields assigned to this entity.
     * A relationship is a field that relates to another entity.
     *
     * @var RelationshipMetadata[]
     */
    public $relationships = [];

    /**
     * Constructor.
     *
     * @param   string  $type   The resource identifier type.
     * @param   string  $idType The identifier data type.
     * @param   string  $idKey  The identifier field key.
     */
    public function __construct($type, $idType = 'string', $idKey = 'id')
    {
        $this->type = $type;
        $this->addAttribute(new AttributeMetadata($idKey, $idType));
    }

    /**
     * Adds an attribute field to this entity.
     *
     * @param   AttributeMetadata   $attribute
     * @return  self
     */
    public function addAttribute(AttributeMetadata $attribute)
    {
        $this->attributes[$attribute->getKey()] = $attribute;
        return $this;
    }

    /**
     * Gets all attribute fields for this entity.
     *
     * @return  AttributeMetadata[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Determines if an attribute field exists on this entity.
     *
     * @param   string  $key
     * @return  bool
     */
    public function hasAttribute($key)
    {
        return null !== $this->getAttribute($key);
    }

    /**
     * Gets an attribute field from this entity.
     * Returns null if the attribute does not exist.
     *
     * @param   string  $key
     * @return  AttributeMetadata|null
     */
    public function getAttribute($key)
    {
        if (!isset($this->attributes[$key])) {
            return null;
        }
        return $this->attributes[$key];
    }

    /**
     * Adds a relationship field to this entity.
     *
     * @param   RelationshipMetadata   $relationship
     * @return  self
     */
    public function addRelationship(RelationshipMetadata $relationship)
    {
        $this->relationships[$relationship->getKey()] = $relationship;
        return $this;
    }

    /**
     * Gets all relationship fields for this entity.
     *
     * @return  RelationshipMetadata[]
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    /**
     * Determines if a relationship field exists on this entity.
     *
     * @param   string  $key
     * @return  bool
     */
    public function hasRelationship($key)
    {
        return null !== $this->getRelationship($key);
    }

    /**
     * Gets a relationship field from this entity.
     * Returns null if the relationship does not exist.
     *
     * @param   string  $key
     * @return  RelationshipMetadata|null
     */
    public function getRelationship($key)
    {
        if (!isset($this->relationships[$key])) {
            return null;
        }
        return $this->relationships[$key];
    }
}

<?php

namespace Zarathustra\JsonApiSerializer\Metadata;

use Zarathustra\JsonApiSerializer\Utility;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

/**
 * Defines serialization metadata for an entity (e.g. a database object).
 * Should be loaded using the MetadataFactory, not instantiated directly.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class EntityMetadata
{
    /**
     * The id key name and type.
     * Per the JSON API spec, it must be 'id' with a data type of 'string.'
     */
    const ID_KEY  = 'id';
    const ID_TYPE = 'string';

    /**
     * Whether this class is considered abstract.
     *
     * @var bool
     */
    public $abstract = false;

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
     */
    public function __construct($type)
    {
        $this->setType($type);
    }

    /**
     * Merges an EntityMetadata instance with this instance.
     * For use with entity class extension.
     *
     * @param   EntityMetadata  $metadata
     * @return  self
     */
    public function merge(EntityMetadata $metadata)
    {
        $this->setType($metadata->type);
        $this->setAbstract($metadata->isAbstract());
        $this->mergeAttributes($metadata->getAttributes());
        $this->mergeRelationships($metadata->getRelationships());
        return $this;
    }

    /**
     * Sets the entity type.
     *
     * @param   string  $type
     * @return  self
     * @throws  InvalidArgumentException If the type is not a string or is empty.
     */
    public function setType($type)
    {
        if (!is_string($type) || empty($type)) {
            throw new InvalidArgumentException('The entity metadata type must be a non-empty string.');
        }
        $this->type = Utility::formatEntityType($type);
        return $this;
    }

    /**
     * Merges attributes with this instance's attributes.
     *
     * @param   array   $toAdd
     * @return  self
     */
    private function mergeAttributes(array $toAdd)
    {
        $this->attributes = array_merge($this->attributes(), $toAdd);
        ksort($this->attributes);
        return $this;
    }

    /**
     * Merges relationships with this instance's relationships.
     *
     * @param   array   $toAdd
     * @return  self
     */
    private function mergeRelationships(array $toAdd)
    {
        $this->relationships = array_merge($this->relationships(), $toAdd);
        ksort($this->relationships);
        return $this;
    }

    /**
     * Whether this metadata represents an abstract class.
     *
     * @return  bool
     */
    public function isAbstract()
    {
        return (Boolean) $this->abstract;
    }

    /**
     * Sets this metadata as representing an abstract class.
     *
     * @param   bool    $bit
     * @return  self
     */
    public function setAbstract($bit = true)
    {
        $this->abstract = (Boolean) $bit;
        return $this;
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
        ksort($this->attributes);
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
        ksort($this->relationships);
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

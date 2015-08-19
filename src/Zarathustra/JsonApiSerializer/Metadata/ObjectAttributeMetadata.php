<?php

namespace Zarathustra\JsonApiSerializer\Metadata;

use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

/**
 * Defines serialization metadata for an attribute whose value is an object (associative array).
 * Should be loaded using the MetadataFactory, not instantiated directly.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class ObjectAttributeMetadata extends AttributeMetadata implements AttributeInterface
{
    /**
     * All attribute fields assigned to this object attribute.
     * An attribute is a "standard" field, such as a string, integer, array, etc.
     *
     * @var AttributeMetadata[]
     */
    public $attributes = [];

    /**
     * Adds an attribute field to this object attribute.
     *
     * @param   AttributeMetadata   $attribute
     * @return  self
     */
    public function addAttribute(AttributeMetadata $attribute)
    {
        $this->validateObjectAttributeKey($attribute->getKey());
        $this->attributes[$attribute->getKey()] = $attribute;
        ksort($this->attributes);
        return $this;
    }

    /**
     * Validates that the object attribute key is valid (not reserved).
     *
     * @param   string  $key
     * @throws  InvalidArgumentException
     */
    protected function validateObjectAttributeKey($key)
    {
        $reserved = ['links', 'relationships'];
        if (in_array(strtolower($key), $reserved)) {
            throw new InvalidArgumentException(sprintf('The field key "%s" is reserved and cannot be used as object attribute field names. Reserved keys are "%s"', $key, implode(', ', $reserved)));
        }
    }

    /**
     * Gets all attribute fields for this object attribute.
     *
     * @return  AttributeMetadata[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Determines any attribute fields exist on this object attribute.
     *
     * @return  bool
     */
    public function hasAttributes()
    {
        return !empty($this->attributes);
    }

    /**
     * Determines if an attribute field exists on this object attribute.
     *
     * @param   string  $key
     * @return  bool
     */
    public function hasAttribute($key)
    {
        return null !== $this->getAttribute($key);
    }

    /**
     * Gets an attribute field from this object attribute.
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
}

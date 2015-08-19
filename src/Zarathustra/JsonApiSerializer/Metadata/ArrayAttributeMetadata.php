<?php

namespace Zarathustra\JsonApiSerializer\Metadata;

use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

/**
 * Defines serialization metadata for an attribute whose value is an array (sequential/numeric array).
 * Should be loaded using the MetadataFactory, not instantiated directly.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class ArrayAttributeMetadata extends AttributeMetadata
{
    /**
     * The array values type, such as mixed, string, integer, float, etc.
     *
     * @var string
     */
    public $valuesType;

    /**
     * Constructor.
     *
     * @param   string  $key        The attribute key name (field name).
     * @param   string  $type       The attribute data type.
     * @param   string  $valuesType The array values type.
     */
    public function __construct($key, $type, $valuesType = 'string')
    {
        parent::__construct($key, $type);
        $this->setValuesType($valuesType);
    }

    /**
     * Sets the array values type, such as mixed, string, integer, etc.
     *
     * @param   string  $valuesType
     * @return  self
     */
    public function setValuesType($valuesType)
    {
        $valuesType = strtolower($valuesType);
        $this->validateValuesType($valuesType);
        $this->valuesType = $valuesType;
        return $this;
    }

    /**
     * Validates the array values type.
     *
     * @todo    Should be derived from the list of available types (as to support custom) not from a static array.
     * @param   string  $type
     * @return  bool
     * @throws  InvalidArgumentException
     */
    protected function validateValuesType($type)
    {
        $valid = ['mixed', 'object', 'boolean', 'date', 'float', 'integer', 'string'];
        if (!in_array($type, $valid)) {
            throw new InvalidArgumentException(sprintf('The array values type "%s" is not valid. Valid types are "%s"', $type, implode(', ', $valid)));
        }
        return true;
    }
}

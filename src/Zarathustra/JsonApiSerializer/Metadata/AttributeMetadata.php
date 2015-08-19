<?php

namespace Zarathustra\JsonApiSerializer\Metadata;

use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

/**
 * Defines serialization metadata for a "standard" field.
 * Should be loaded using the MetadataFactory, not instantiated directly.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class AttributeMetadata extends FieldMetadata
{
    /**
     * The attribute type, such as string, integer, float, etc.
     *
     * @var string
     */
    public $type;

    /**
     * Constructor.
     *
     * @param   string  $type   The attribute data type.
     */
    public function __construct($key, $type)
    {
        parent::__construct($key);
        $this->setType($type);
    }

    /**
     * Sets the attribute type, such as string, integer, etc.
     *
     * @param   string  $type
     * @return  self
     */
    public function setType($type)
    {
        $this->validateType($type);
        $this->type = strtolower($type);
        return $this;
    }

    /**
     * Validates the attribute type.
     *
     * @todo    Should be derived from the list of available types (as to support custom) not from a static array.
     * @param   string  $type
     * @return  bool
     * @throws  \InvalidArgumentException
     */
    protected function validateType($type)
    {
        $type = (String) $type;
        $valid = ['array', 'object', 'boolean', 'date', 'float', 'integer', 'string'];
        $type = strtolower($type);
        if (!in_array($type, $valid)) {
            throw new InvalidArgumentException(sprintf('The relationship type "%s" is not valid. Valid types are "%s"', $type, implode(', ', $valid)));
        }
        return true;
    }
}

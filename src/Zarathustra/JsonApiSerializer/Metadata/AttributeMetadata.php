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
        $this->type = $type;
        return $this;
    }
}

<?php

namespace Zarathustra\JsonApiSerializer\Metadata;

/**
 * Abstract serialization metadata for entity fields.
 * Should be loaded using the MetadataFactory, not instantiated directly.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
abstract class FieldMetadata
{
    /**
     * The field key.
     *
     * @var string
     */
    public $key;

    /**
     * Constructor.
     *
     * @param   string  $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Gets the field key.
     *
     * @return  string
     */
    public function getKey()
    {
        return $this->key;
    }
}

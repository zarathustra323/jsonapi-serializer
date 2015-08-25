<?php

namespace Zarathustra\JsonApiSerializer\Metadata;

use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

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
     * Whether or not this field should be serialized.
     *
     * @var bool
     */
    public $serialize = true;

    /**
     * Constructor.
     *
     * @param   string  $key
     */
    public function __construct($key)
    {
        $this->validateKey($key);
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

    /**
     * Validates that the field key is not reserved.
     *
     * @param   string  $key
     * @throws  InvalidArgumentException
     */
    protected function validateKey($key)
    {
        $reserved = ['type', 'id'];
        if (in_array(strtolower($key), $reserved)) {
            throw new InvalidArgumentException(sprintf('The field key "%s" is reserved and cannot be used. Reserved keys are "%s"', $key, implode(', ', $reserved)));
        }
    }
}

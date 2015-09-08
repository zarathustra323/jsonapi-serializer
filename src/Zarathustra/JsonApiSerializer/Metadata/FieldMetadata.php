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
     * The externally formatted field key.
     *
     * @var string
     */
    public $externalKey;

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
     * Whether this field should be included in serialization.
     *
     * @return  bool
     */
    public function shouldSerialize()
    {
        return (Boolean) $this->serialize;
    }

    /**
     * Sets whether this field should be serialized.
     *
     * @param   bool    $bit
     * @return  self
     */
    public function setSerialize($bit = true)
    {
        $this->serialize = (Boolean) $bit;
        return $this;
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

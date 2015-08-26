<?php

namespace Zarathustra\JsonApiSerializer\DataTypes\Types;

/**
 * The boolean data type converter.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class BooleanType implements TypeInterface
{
    /**
     * {@inheritDoc}
     */
    public function convertToApiValue($value)
    {
        if (is_object($value)) {
            return (Boolean) (String) $value;
        }
        if (null !== $value) {
            return (Boolean) $value;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value)
    {
        if (is_object($value)) {
            return (Boolean) (String) $value;
        }
        if (null !== $value) {
            return (Boolean) $value;
        }
        return null;
    }
}

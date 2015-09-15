<?php

namespace Zarathustra\ModlrData\DataTypes\Types;

/**
 * The float data type converter.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class FloatType implements TypeInterface
{
    /**
     * {@inheritDoc}
     */
    public function convertToModlrValue($value)
    {
        if (is_object($value)) {
            return (Float) (String) $value;
        }
        if (null !== $value) {
            return (Float) $value;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value)
    {
        if (is_object($value)) {
            return (Float) (String) $value;
        }
        if (null !== $value) {
            return (Float) $value;
        }
        return null;
    }
}

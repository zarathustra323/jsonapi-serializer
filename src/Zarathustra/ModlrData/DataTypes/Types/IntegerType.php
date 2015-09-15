<?php

namespace Zarathustra\ModlrData\DataTypes\Types;

/**
 * The integer data type converter.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class IntegerType implements TypeInterface
{
    /**
     * {@inheritDoc}
     */
    public function convertToModlrValue($value)
    {
        if (is_object($value)) {
            return (Integer) (String) $value;
        }
        if (null !== $value) {
            return (Integer) $value;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value)
    {
        if (is_object($value)) {
            return (Integer) (String) $value;
        }
        if (null !== $value) {
            return (Integer) $value;
        }
        return null;
    }
}

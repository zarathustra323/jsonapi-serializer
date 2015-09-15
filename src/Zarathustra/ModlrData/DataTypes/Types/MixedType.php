<?php

namespace Zarathustra\ModlrData\DataTypes\Types;

/**
 * The mixed data type converter.
 * Actually doesn't convert anything, just passes the raw value.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class MixedType implements TypeInterface
{
    /**
     * {@inheritDoc}
     */
    public function convertToModlrValue($value)
    {
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value)
    {
        return $value;
    }
}

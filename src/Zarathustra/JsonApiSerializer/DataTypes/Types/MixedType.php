<?php

namespace Zarathustra\JsonApiSerializer\DataTypes\Types;

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
    public function convertToApiValue($value)
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

<?php

namespace Zarathustra\JsonApiSerializer\DataTypes\Types;

/**
 * The type interface that all data type objects must use.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
interface TypeInterface
{
    /**
     * Converts the value from PHP to the external JSON API value.
     *
     * @param   mixed   $value
     * @return  mixed
     */
    public function convertToApiValue($value);

    /**
     * Converts the value from external JSON API value to the PHP value.
     *
     * @param   mixed   $value
     * @return  mixed
     */
    public function convertToPHPValue($value);
}

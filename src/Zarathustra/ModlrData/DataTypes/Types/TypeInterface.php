<?php

namespace Zarathustra\ModlrData\DataTypes\Types;

/**
 * The type interface that all data type objects must use.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
interface TypeInterface
{
    /**
     * Converts the value from Modlr to the external JSON API value.
     *
     * @param   mixed   $value
     * @return  mixed
     */
    public function convertToModlrValue($value);

    /**
     * Converts the value from external Modlr value to the PHP value.
     *
     * @param   mixed   $value
     * @return  mixed
     */
    public function convertToPHPValue($value);
}

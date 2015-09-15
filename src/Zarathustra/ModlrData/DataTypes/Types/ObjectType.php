<?php

namespace Zarathustra\ModlrData\DataTypes\Types;

/**
 * The object data type converter.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class ObjectType implements TypeInterface
{
    /**
     * {@inheritDoc}
     */
    public function convertToModlrValue($value)
    {
        return $this->extractObject($value);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value)
    {
        return $this->extractObject($value);
    }

    /**
     * Takes a value and converts it to an object.
     *
     * @param   mixed   $value
     * @return  array
     */
    protected function extractObject($value)
    {
        if (empty($value)) {
            return (Object) [];
        }

        if ($value instanceof \Traversable) {
            $array = [];
            foreach ($value as $key => $value) {
                $array[$key] = $value;
            }
            return (Object) $array;
        }

        return (Object) $value;
    }
}

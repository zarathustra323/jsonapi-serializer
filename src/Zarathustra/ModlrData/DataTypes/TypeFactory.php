<?php

namespace Zarathustra\ModlrData\DataTypes;

use Zarathustra\ModlrData\DataTypes\Types\TypeInterface;
use Zarathustra\ModlrData\Exception\InvalidArgumentException;

/**
 * Responsible for loading attribute data type classes.
 * Each data type class converts attribute values between Modlr format and PHP.
 * Built-in type: array, object, boolean, date, float, integer, string.
 * You can also register custom types.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class TypeFactory
{
    /**
     * Available data types.
     * Mapped by the type name/key to fully-qualified class name.
     * The class must extend the abstract DataTypes\Types\Type class.
     *
     * @var array
     */
    private $types = [
        'array'     => 'Zarathustra\ModlrData\DataTypes\Types\ArrayType',
        'boolean'   => 'Zarathustra\ModlrData\DataTypes\Types\BooleanType',
        'date'      => 'Zarathustra\ModlrData\DataTypes\Types\DateType',
        'float'     => 'Zarathustra\ModlrData\DataTypes\Types\FloatType',
        'integer'   => 'Zarathustra\ModlrData\DataTypes\Types\IntegerType',
        'mixed'     => 'Zarathustra\ModlrData\DataTypes\Types\MixedType',
        'object'    => 'Zarathustra\ModlrData\DataTypes\Types\ObjectType',
        'string'    => 'Zarathustra\ModlrData\DataTypes\Types\StringType',
    ];

    /**
     * In-memory loaded type objects.
     *
     * @var array
     */
    private $loaded = [];

    /**
     * Converts the value from PHP to the external Modlr value.
     *
     * @param   string  $name   The data type name.
     * @param   mixed   $value  The value to convert.
     * @return  mixed
     */
    public function convertToModlrValue($name, $value)
    {
        return $this->getType($name)->convertToModlrValue($value);
    }

    /**
     * Converts the value from external Modlr value to the PHP value.
     *
     * @param   string  $name   The data type name.
     * @param   mixed   $value  The value to convert.
     * @return  mixed
     */
    public function convertToPHPValue($name, $value)
    {
        return $this->getType($name)->convertToPHPValue($value);
    }

    /**
     * Gets all registered types by name/key.
     *
     * @return  array
     */
    public function getTypes()
    {
        return array_keys($this->types);
    }

    /**
     * Gets a type object.
     *
     * @param   string      $name
     * @return  DataTypes\Types\Type
     * @throws  InvalidArgumentException    If the type wasn't found.
     */
    public function getType($name)
    {
        if (false === $this->hasType($name)) {
            throw new InvalidArgumentException(sprintf('The type "%s" was not found.', $name));
        }
        if (isset($this->loaded[$name])) {
            return $this->loaded[$name];
        }
        $fqcn = $this->types[$name];
        $type = new $fqcn;

        if (!$type instanceof TypeInterface) {
            throw new InvalidArgumentException(sprintf('The class "%s" must implement the "%s\\Types\TypeInterface"', $fqcn, __NAMESPACE__));
        }

        return $this->loaded[$name] = new $fqcn;
    }

    /**
     * Adds a type object.
     *
     * @param   string      $name
     * @param   string      $fqcn
     * @return  self
     * @throws  InvalidArgumentException    If the type already exists.
     */
    public function addType($name, $fqcn)
    {
        if (true === $this->hasType($name)) {
            throw new InvalidArgumentException(sprintf('The type "%s" already exists.', $name));
        }
        return $this->setType($name, $fqcn);
    }

    /**
     * Overrides a type object with new class.
     *
     * @param   string      $name
     * @param   string      $fqcn
     * @return  self
     * @throws  InvalidArgumentException    If the type was not found.
     */
    public function overrideType($name, $fqcn)
    {
        if (false === $this->hasType($name)) {
            throw new InvalidArgumentException(sprintf('The type "%s" was not found.', $name));
        }
        return $this->setType($name, $fqcn);
    }

    /**
     * Sets a type.
     *
     * @param   string      $name
     * @param   string      $fqcn
     * @return  self
     */
    private function setType($name, $fqcn)
    {
        $this->types[$name] = $fqcn;
        return $this;
    }

    /**
     * Determines if a type exists.
     *
     * @param   string      $name
     * @return  bool
     */
    public function hasType($name)
    {
        return isset($this->types[$name]);
    }
}

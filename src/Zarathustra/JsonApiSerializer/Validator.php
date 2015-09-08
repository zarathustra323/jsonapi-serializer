<?php

namespace Zarathustra\JsonApiSerializer;

use Zarathustra\JsonApiSerializer\DataTypes\TypeFactory;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

/**
 * Responsibile for validating common components of the serializer.
 * Common tasks include validating entity name formats, namespaces, strings, etc.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class Validator
{
    /**
     * The attribute data type factory.
     * Is used to ensure data types are registered.
     *
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * Valid entity namespace delimiters.
     *
     * @var     array
     */
    private $namespaceDelimiters = ['_', '-', '__', '--'];

    /**
     * Valid entity string formats.
     *
     * @var array
     */
    private $stringFormats = ['dash', 'camelcase', 'studlycaps', 'underscore'];

    /**
     * Characters that are not allowed as starting or ending characters in member names.
     *
     * @var string
     */
    private $invalidBookends = '/^[-_ ]|.*[-_ ]$/i';

    /**
     * Characters the are globally disallowed in all member names
     *
     * @var string
     */
    private $invalidMemberChars = '/.*[\x{0000}-\x{001F}\x{0021}-\x{002C}\x{002E}-\x{002F}\x{003A}-\x{0040}\x{005B}-\x{005E}\x{0060}\x{007B}-\x{007F}].*/is';

    /**
     * Constructor.
     *
     * @param   TypeFactory     $typeFactory
     */
    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    /**
     * Validates a namespace delimiter.
     *
     * @param   string  $delimiter
     * @return  bool
     * @throws  InvalidArgumentException
     */
    public function validateNamespaceDelimiter($delimiter)
    {
        if (!in_array($delimiter, $this->namespaceDelimiters)) {
            throw new InvalidArgumentException(sprintf('The namespace delimiter "%s" is invalid. Valid delimiters are "%s"', $delimiter, implode(', ', $this->namespaceDelimiters)));
        }
        return true;
    }

    /**
     * Validates a member name (such as entity types or entity field keys).
     *
     * @param   string  $name
     * @return  bool
     * @throws  InvalidArgumentException
     */
    public function validateMemberName($name)
    {
        $name = iconv(mb_detect_encoding($name), 'UTF-8', $name);
        if (empty($name)) {
            throw new InvalidArgumentException('A member name cannot be empty.');
        }

        if (preg_match($this->invalidMemberChars, $name, $matches)) {
            throw new InvalidArgumentException('A member name contains an invalid character.');
        }

        if (preg_match($this->invalidBookends, $name)) {
            throw new InvalidArgumentException('A member name cannot start or end with the following characters: "-", "_", " "');
        }
        return true;
    }

    /**
     * Validates that the given attribute data type exists.
     *
     * @param   string  $dataType
     * @return  bool
     * @throws  InvalidArgumentException
     */
    public function validateDataType($dataType)
    {
        if (false === $this->typeFactory->hasType($dataType)) {
            throw new InvalidArgumentException(sprintf('The data type "%s" does not exist.', $dataType));
        }
        return true;
    }

    /**
     * Validates a namespace delimiter compared to a selected name format.
     *
     * @param   string  $delimiter
     * @param   string  $nameFormat
     * @return  bool
     * @throws  InvalidArgumentException
     */
    public function validateNameFormat($delimiter, $nameFormat)
    {
        if (('_' === $delimiter && 'underscore' === $nameFormat) || ('-' === $delimiter && 'dash' === $nameFormat)) {
            throw new InvalidArgumentException(sprintf('You cannot use the namespace delimiter "%s" when using the entity name format of "%s"', $delimiter, $nameFormat));
        }
        return true;
    }

    /**
     * Validates an entity string format.
     *
     * @param   string  $format
     * @return  bool
     * @throws  InvalidArgumentException
     */
    public function validateStringFormat($format)
    {
        if (!in_array($format, $this->stringFormats)) {
            throw new InvalidArgumentException(sprintf('The string format "%s" is invalid. Valid formats are "%s"', $format, implode(', ', $this->stringFormats)));
        }
        return true;
    }
}

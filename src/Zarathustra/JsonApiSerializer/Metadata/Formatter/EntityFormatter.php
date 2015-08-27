<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Formatter;

use Zarathustra\JsonApiSerializer\Validator;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;
use Zarathustra\Common\Inflector;

/**
 * Utility class for handling entity type formatting and naming.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class EntityFormatter
{
    /**
     * Validator component for ensuring formats are correct.
     *
     * @var Validator
     */
    private $validator;

    /**
     * The inflector for converting string formats
     *
     * @var Inflector
     */
    private $inflector;

    /**
     * Constructor.
     *
     * @param   Validator|null  $validator
     * @param   Inflector|null  $inflector
     */
    public function __construct(Validator $validator = null, Inflector $inflector = null)
    {
        $this->validator = $validator ?: new Validator();
        $this->inflector = $inflector ?: new Inflector();
    }

    /**
     * Formats an entity type for internal usage.
     * Should be used for file names and cache keys and any other internal usage.
     *
     * @param   string  $type
     * @return  string
     */
    public function getInternalType($type)
    {
        return $this->formatType($type, 'studlycaps', '\\');
    }

    /**
     * Gets the filename for an entity type.
     *
     * @param   $type
     * @return  string
     */
    public function getFilename($type)
    {
        return str_replace('\\', '_', $this->getInternalType($type));
    }

    /**
     * Formats the entity type name.
     *
     * @param   string      $type
     * @param   string      $format
     * @param   string|null $namespaceDelimiter
     * @return  string
     */
    public function formatType($type, $format, $namespaceDelimiter = null)
    {
        if (null === $namespaceDelimiter) {
            return $this->doFormat($type, $format);
        }
        $parts = explode($namespaceDelimiter, $type);
        foreach ($parts as &$part) {
            $part = $this->doFormat($part, $format);
        }
        return implode($namespaceDelimiter, $parts);
    }

    /**
     * Formats an entity field (attribute/relationship) key.
     *
     * @param   string  $key
     * @param   string  $format
     * @return  string
     */
    public function formatField($key, $format)
    {
        return $this->doFormat($key, $format);
    }

    /**
     * Formats a string.
     *
     * @param   string  $string
     * @param   string  $format
     * @return  string
     * @throws  InvalidArgumentException If the format cannot be handled.
     */
    protected function doFormat($string, $format)
    {
        $this->validator->validateStringFormat($format);
        switch ($format) {
            case 'underscore':
                return $this->inflector->underscore($string);
            case 'camelcase':
                return $this->inflector->camelize($string);
            case 'studlycaps':
                return $this->inflector->studlify($string);
            case 'dash':
                return $this->inflector->dasherize($string);
            default:
                throw new InvalidArgumentException(sprintf('Unable to load a string formatter for format type "%s"', $format));
        }
    }
}

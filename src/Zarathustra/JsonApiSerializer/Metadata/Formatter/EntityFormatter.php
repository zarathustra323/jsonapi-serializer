<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Formatter;

use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;
use Zarathustra\JsonApiSerializer\Metadata\Configuration;
use Zarathustra\Common\Inflector;

/**
 * Utility class for handling entity type formatting and naming.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class EntityFormatter
{
    /**
     * The metadata configuration.
     *
     * @var Configuration
     */
    private $config;

    /**
     * The inflector for converting string formats
     *
     * @var Inflector
     */
    private $inflector;

    /**
     * Constructor.
     *
     * @param   Configuration|null  $config
     */
    public function __construct(Configuration $config = null, Inflector $inflector = null)
    {
        $this->config = $config ?: new Configuration();
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
            return $this->doFormatType($type, $format);
        }
        $parts = explode($namespaceDelimiter, $type);
        foreach ($parts as &$part) {
            $part = $this->doFormatType($part, $format);
        }
        return implode($namespaceDelimiter, $parts);
    }

    /**
     * Formats the type.
     *
     * @param   string  $type
     * @param   string  $format
     * @return  string
     * @throws  InvalidArgumentException If the format cannot be handled.
     */
    protected function doFormatType($type, $format)
    {
        $this->config->validateStringFormat($format);
        switch ($format) {
            case 'underscore':
                return $this->inflector->underscore($type);
            case 'camelcase':
                return $this->inflector->camelize($type);
            case 'studlycaps':
                return $this->inflector->studlify($type);
            case 'dash':
                return $this->inflector->dasherize($type);
            default:
                throw new InvalidArgumentException(sprintf('Unable to load an entity type formatter for type "%s"', $format));
        }
    }
}

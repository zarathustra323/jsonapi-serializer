<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Formatter;

use Zarathustra\JsonApiSerializer\Configuration;
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
     * The inflector for converting string formats
     *
     * @var Inflector
     */
    private $inflector;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * Constructor.
     *
     * @param   Configuration   $config
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->inflector = new Inflector();
    }

    /**
     * Formats an entity type for internal usage.
     * Should be used for file names and cache keys and any other internal usage.
     *
     * @param   string  $type
     * @return  string
     */
    public function formatInternalEntityType($type)
    {
        return $this->formatType($type, 'studlycaps', Configuration::INTERNAL_NS_DELIM);
    }

    /**
     * Formats an entity type name to the external format, based on config.
     *
     * @param   string  $type
     * @return  string
     */
    public function formatExternalEntityType($type)
    {
        $format = $this->config->getEntityNameFormat();
        $delim  = $this->config->getNamespaceDelimiter();

        $type = $this->formatType($type, $format, Configuration::INTERNAL_NS_DELIM);
        $type = str_replace(Configuration::INTERNAL_NS_DELIM, $delim, $type);

        $this->config->getValidator()->validateMemberName($type);
        return $type;
    }

    public function getExternalType($type, $format, $delim)
    {
        $type = $this->formatType($type, $format, Configuration::INTERNAL_NS_DELIM);
        return str_replace(Configuration::INTERNAL_NS_DELIM, $delim, $type);
    }

    /**
     * Gets the file base name for an entity type.
     *
     * @param   string  $type
     * @return  string
     */
    public function getFileBaseName($type)
    {
        return str_replace(Configuration::INTERNAL_NS_DELIM, '_', $this->formatInternalEntityType($type));
    }

    /**
     * Gets the entiy type from a file base name.
     *
     * @param   string  $baseName
     * @return  string
     */
    public function getTypeFromFileBaseName($baseName)
    {
        return $this->formatInternalEntityType(str_replace('_', Configuration::INTERNAL_NS_DELIM, $baseName));
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
     * @param   string  $format
     * @return  string
     */
    public function formatField($key)
    {
        $format = $this->config->getFieldKeyFormat();

        $key = $this->doFormat($key, $format);
        $this->config->getValidator()->validateMemberName($key);
        return $key;
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
        $this->config->getValidator()->validateStringFormat($format);
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

<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Formatter;

use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;
use Zarathustra\JsonApiSerializer\Metadata\Configuration;
use Zarathustra\Common\StringUtils;

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
     * Constructor.
     *
     * @param   Configuration|null  $config
     */
    public function __construct(Configuration $config = null)
    {
        $this->config = $config ?: new Configuration();
    }

    /**
     * Formats an entity type for internal usage.
     * Should be used for file names and cache keys and any other internal usage.
     *
     * @static
     * @param   string  $type
     * @return  string
     */
    public static function getInternalType($type)
    {
        return self::formatType($type, 'studlycaps', '\\');
    }

    /**
     * Gets the filename for an entity type.
     *
     * @static
     * @param   $type
     * @return  string
     */
    public static function getFilename($type)
    {
        return str_replace('\\', '_', self::getInternalType($type));
    }

    /**
     * Formats the entity type name.
     *
     * @param   string      $type
     * @param   string      $format
     * @param   string|null $namespaceDelimiter
     * @return  string
     */
    protected static function formatType($type, $format, $namespaceDelimiter = null)
    {
        $formatter = self::getTypeFormatter($format);
        if (null === $namespaceDelimiter) {
            return $formatter($type);
        }
        $parts = explode($namespaceDelimiter, $type);
        foreach ($parts as &$part) {
            $part = $formatter($part);
        }
        return implode($namespaceDelimiter, $parts);
    }

    /**
     * Gets the type formatter function.
     *
     * @param   string|null $format
     * @return  \Closure
     * @throws  InvalidArgumentException
     */
    public static function getTypeFormatter($format)
    {
        Configuration::validateStringFormat($format);
        return function($type) use ($format) {
            switch ($format) {
                case 'underscore':
                    return StringUtils::underscore($type);
                case 'camelcase':
                    return StringUtils::camelize($type);
                case 'studlycaps':
                    return StringUtils::studlify($type);
                case 'dash':
                    return StringUtils::dasherize($type);
                default:
                    throw new InvalidArgumentException(sprintf('Unable to load an entity type formatter for type "%s"', $format));
            }
        };
    }
}

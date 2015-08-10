<?php

namespace Zarathustra\JsonApiSerializer;

class Utility
{
    const NAMESPACE_DELIMITER = '/';

    /**
     * Prevent instantiation.
     * @access private
     */
    private function __construct() { }

    /**
     * Determines if an entity type is namespaced.
     *
     * @static
     * @param   string  $type
     * @return  bool
     */
    public static function isEntityTypeNamespaced($type)
    {
        return false !== stristr($type, self::NAMESPACE_DELIMITER);
    }

    /**
     * Formats the entity type.
     *
     * @static
     * @param   string  $type
     * @return  string
     */
    public static function formatEntityType($type)
    {
        if (false === self::isEntityTypeNamespaced($type)) {
            return self::dasherize($type);
        }
        $parts = explode(self::NAMESPACE_DELIMITER, $type);
        foreach ($parts as &$part) {
            $part = self::dasherize($part);
        }
        return implode(self::NAMESPACE_DELIMITER, $parts);
    }

    /**
     * Convert word into underscore format (e.g. some_name_here).
     *
     * @static
     * @param   string  $word
     * @return  string
     */
    public static function underscore($word)
    {
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $word));
    }

    /**
     * Convert word into dasherized format (e.g. some-name-here).
     *
     * @static
     * @param   string  $word
     * @return  string
     */
    public static function dasherize($word)
    {
        return str_replace('_', '-', self::underscore($word));
    }

    /**
     * Convert word into camelized format (e.g. someNameHere).
     *
     * @static
     * @param   string  $word
     * @return  string
     */
    public static function camelize($word)
    {
        return lcfirst(self::studlify($word));
    }

    /**
     * Convert word into studly caps format (e.g. SomeNameHere).
     *
     * @static
     * @param   string  $word
     * @return  string
     */
    public static function studlify($word)
    {
        return str_replace(" ", "", ucwords(strtr(self::underscore($word), "_", " ")));
    }
}

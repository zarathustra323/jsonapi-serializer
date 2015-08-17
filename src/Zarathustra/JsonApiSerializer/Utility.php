<?php

namespace Zarathustra\JsonApiSerializer;

use Zarathustra\Common\StringUtils;

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
            return StringUtils::dasherize($type);
        }
        $parts = explode(self::NAMESPACE_DELIMITER, $type);
        foreach ($parts as &$part) {
            $part = StringUtils::dasherize($part);
        }
        return implode(self::NAMESPACE_DELIMITER, $parts);
    }
}

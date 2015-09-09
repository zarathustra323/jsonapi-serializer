<?php

namespace Zarathustra\JsonApiSerializer\Exception;

/**
 * Metadata exceptions.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class MetadataException extends \Exception implements ExceptionInterface
{
    public static function mappingNotFound($type)
    {
        return new self(sprintf('Unable to locate metadata mapping information for entity type "%s"', $type));
    }
}

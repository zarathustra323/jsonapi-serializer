<?php

namespace Zarathustra\ModlrData\Exception;

/**
 * Metadata exceptions.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class MetadataException extends \Exception implements ExceptionInterface
{
    public static function mappingNotFound($entityType)
    {
        return new self(sprintf('Unable to locate metadata mapping information for Entity type "%s"', $entityType));
    }

    public static function invalidEntityType($entityType)
    {
        return new self(sprintf('The provided Entity type "%s" is invalid.', $entityType));
    }

    public static function fieldKeyInUse($attemptedKeyType, $existsKeyType, $fieldKey, $entityType)
    {
        throw new self(sprintf(
            'The %s key "%s" already exists as a(n) %s. A(n) %s cannot have the same key as a(n) %s on Entity type "%s"',
            $attemptedKeyType,
            $fieldKey,
            $existsKeyType,
            $attemptedKeyType,
            $existsKeyType,
            $entityType
        ));
    }

    public static function reservedFieldKey($key, array $reserved)
    {
        throw new self(sprintf('The field key "%s" is reserved and cannot be used. Reserved keys are "%s"', $key, implode(', ', $reserved)));
    }

    public static function invalidRelType($relType, array $valid)
    {
        throw new self(sprintf('The relationship type "%s" is not valid. Valid types are "%s"', $relType, implode(', ', $valid)));
    }
}

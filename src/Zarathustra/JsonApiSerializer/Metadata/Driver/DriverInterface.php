<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Driver;

/**
 * Interface for metadata driver implementations.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
interface DriverInterface
{
    /**
     * Loads the EntityMetadata for a type.
     *
     * @param   string  $type
     * @return  \Zarathustra\JsonApiSerializer\Metadata\EntityMetadata
     * @throws  \Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException If the type cannot be found.
     */
    public function loadMetadataForType($type);

    /**
     * Gets all type names.
     *
     * @return  array
     */
    public function getAllTypeNames();

    /**
     * Gets the type hierarchy (via extension) for an entity type.
     * Is recursive.
     *
     * @param   string  $type
     * @param   array   $types
     * @return  array
     * @throws  \Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException If any types in the tree cannot be found or parsed.
     */
    public function getTypeHierarchy($type, array $types = []);
}

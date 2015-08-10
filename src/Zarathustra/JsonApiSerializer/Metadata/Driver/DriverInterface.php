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
     * @return  \Zarathustra\JsonApiSerializer\Metadata\EntityMetadata|null
     */
    public function loadMetadataForType($type);

    /**
     * Gets all type names.
     *
     * @return  array
     */
    public function getAllTypeNames();
}

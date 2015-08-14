<?php

namespace Zarathustra\JsonApiSerializer\Metadata;

/**
 * Defines the implementation of a Metadata Factory object.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
interface MetadataFactoryInterface
{
    /**
     * Returns an entity metadata object for the given entity type.
     *
     * @param   string              $type
     * @return  EntityMetadata
     */
    public function getMetadataForType($type);

    /**
     * Gets all available entity type names.
     *
     * @return  array
     */
    public function getAllTypeNames();
}

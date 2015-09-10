<?php

namespace Zarathustra\ModlrData\Metadata;

/**
 * Defines the implementation of a MetadataFactory object.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
interface MetadataFactoryInterface
{
    /**
     * Returns EntityMetadata for the given entity type.
     *
     * @param   string              $type
     * @return  EntityMetadata
     * @throws  \Zarathustra\ModlrData\Exception\MetadataException  If metadata was not found.
     */
    public function getMetadataForType($type);

    /**
     * Gets all EntityMetadata for known entities, keyed by entity type.
     *
     * @return  EntityMetadata[]
     */
    public function getAllMetadata();

    /**
     * Determines if EntityMetadata exists for the given entity type.
     *
     * @return  bool
     */
    public function metadataExists($type);

    /**
     * Gets all available Entity type names.
     *
     * @return  array
     */
    public function getAllTypeNames();
}

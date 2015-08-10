<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Driver;

/**
 * Interface for locating metadata files for generating Metadata classes.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
interface FileLocatorInterface
{
    /**
     * Finds the file location for a metadata file (for loading an EntityMetadata class instance), based on entity type.
     *
     * @param   string  $type
     * @param   string  $extension
     *
     * @return  string|null
     */
    public function findFileForType($type, $extension);

    /**
     * Finds all possible metadata files.
     *
     * @param   string  $extension
     * @return  array
     */
    public function findAllTypes($extension);
}

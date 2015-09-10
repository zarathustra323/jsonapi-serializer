<?php

namespace Zarathustra\ModlrData\Metadata\Cache;

use Zarathustra\ModlrData\Metadata\EntityMetadata;

/**
 * Metadata Cache Interface.
 * Can be implemented to support different sources, such as file, Redis, etc.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
interface CacheInterface
{
    /**
     * Loads an EntityMetadata object from the cache source.
     * Should return null if the object is not found.
     *
     * @param   string              $type
     * @return  EntityMetadata|null
     */
    public function loadMetadataFromCache($type);

    /**
     * Puts a EntityMetadata object into the cache source.
     *
     * @param   EntityMetadata  $metadata
     * @return  self
     */
    public function putMetadataInCache(EntityMetadata $metadata);

    /**
     * Removes an EntityMetadata object from the cache source.
     *
     * @param   EntityMetadata   $metadata
     * @return  bool
     */
    public function evictMetadataFromCache(EntityMetadata $metadata);
}

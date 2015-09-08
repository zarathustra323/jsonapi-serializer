<?php

namespace Zarathustra\JsonApiSerializer\Metadata;

use Zarathustra\JsonApiSerializer\Metadata\Formatter\EntityFormatter;
use Zarathustra\JsonApiSerializer\Metadata\Driver\DriverInterface;
use Zarathustra\JsonApiSerializer\Metadata\Cache\CacheInterface;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

/**
 * The primary Metadata Factory service.
 * Returns EntityMetadata instances for supplied entity types.
 * Can also write and retrieve these instances from cache, if supplied.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * The Metadata driver.
     *
     * @var DriverInterface
     */
    private $driver;

    /**
     * The Metadata cache instance.
     * Is optional, if defined using the setter.
     *
     * @var CacheInterface
     */
    private $cache;

    /**
     * Flags whether metadata caching is enabled.
     *
     * @var bool
     */
    private $cacheEnabled = true;

    /**
     * In-memory loaded Metadata instances.
     *
     * @var EntityMetadata[]
     */
    private $loaded;

    /**
     * The entity formatter utility.
     *
     * @var EntityFormatter
     */
    private $entityFormatter;

    /**
     * Constructor.
     *
     * @param   DriverInterface $driver
     * @param   EntityFormatter $entityFormatter
     */
    public function __construct(DriverInterface $driver, EntityFormatter $entityFormatter)
    {
        $this->driver = $driver;
        $this->entityFormatter = $entityFormatter;
    }

    /**
     * Gets the entity formatter.
     *
     * @return  EntityFormatter
     */
    public function getEntityFormatter()
    {
        return $this->entityFormatter;
    }

    /**
     * Sets the cache instance to use for reading/writing Metadata objects.
     *
     * @param   CacheInterface  $cache
     * @return  self
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Gets the cache instance.
     *
     * @return  CacheInterface|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Enables or disables the cache.
     *
     * @param   bool    $bit
     * @return  self
     */
    public function enableCache($bit = true)
    {
        $this->cacheEnabled = (Boolean) $bit;
        return $this;
    }

    /**
     * Determines if cache is enbled.
     *
     * @return  bool
     */
    public function hasCache()
    {
        return null !== $this->getCache() && true === $this->cacheEnabled;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataForType($type)
    {
        $type = $this->entityFormatter->formatInternalEntityType($type);
        if (null !== $metadata = $this->doLoadMetadata($type)) {
            // Found in memory or from cache implementation
            return $metadata;
        }

        // Loop through the type hierarchy (extension) and merge metadata objects.
        foreach ($this->driver->getTypeHierarchy($type) as $hierType) {
            $hierType = $this->entityFormatter->formatInternalEntityType($hierType);

            if (null !== $loaded = $this->doLoadMetadata($hierType)) {
                // Found in memory or from cache implementation
                $this->mergeMetadata($metadata, $loaded);
                continue;
            }

            // Load from driver source
            $loaded = $this->driver->loadMetadataForType($hierType);

            // Format (and validate) the external entity type and set.
            $loaded->externalType = $this->entityFormatter->formatExternalEntityType($hierType);

            // Format (and validate) the external field keys for attributes and relationships.
            foreach ($loaded->getAttributes() as $attribute) {
                $attribute->externalKey = $this->entityFormatter->formatField($attribute->key);
            }
            foreach ($loaded->getRelationships() as $relationship) {
                $relationship->externalKey = $this->entityFormatter->formatField($relationship->key);
            }

            $this->mergeMetadata($metadata, $loaded);
            $this->doPutMetadata($loaded);
        }

        $this->doPutMetadata($metadata);
        return $metadata;
    }

    /**
     * Merges two sets of EntityMetadata.
     * Is used for applying inheritance information.
     *
     * @param   EntityMetadata  &$metadata
     * @param   EntityMetadata  $toAdd
     */
    private function mergeMetadata(EntityMetadata &$metadata = null, EntityMetadata $toAdd)
    {
        if (null === $metadata) {
            $metadata = clone $toAdd;
        } else {
            $metadata->merge($toAdd);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAllTypeNames()
    {
        return $this->driver->getAllTypeNames();
    }

    /**
     * Attempts to load a Metadata instance from a memory or cache source.
     *
     * @param   string  $type
     * @return  EntityMetadata|null
     */
    private function doLoadMetadata($type)
    {
        if (null !== $meta = $this->getFromMemory($type)) {
            // Found in memory.
            return $meta;
        }

        if (null !== $meta = $this->getFromCache($type)) {
            // Found in cache.
            $this->setToMemory($meta);
            return $meta;
        }
        return null;
    }

    /**
     * Puts the Metadata instance into a cache source (if set) and memory.
     *
     * @param   EntityMetadata  $metadata
     * @return  self
     */
    private function doPutMetadata(EntityMetadata $metadata)
    {
        if (true === $this->hasCache()) {
            $this->cache->putMetadataInCache($metadata);
        }
        $this->setToMemory($metadata);
        return $this;
    }

    /**
     * Clears any loaded metadata objects from memory.
     *
     * @return  self
     */
    public function clearMemory()
    {
        $this->loaded = [];
        return $this;
    }

    /**
     * Gets a Metadata instance for a type from memory.
     *
     * @return  EntityMetadata|null
     */
    private function getFromMemory($type)
    {
        if (isset($this->loaded[$type])) {
            return $this->loaded[$type];
        }
        return null;
    }

    /**
     * Sets a Metadata instance to the memory cache.
     *
     * @param   EntityMetadata  $metadata
     * @return  self
     */
    private function setToMemory(EntityMetadata $metadata)
    {
        $this->loaded[$metadata->type] = $metadata;
        return $this;
    }

    /**
     * Retrieves a Metadata instance for a type from cache.
     *
     * @return  EntityMetadata|null
     */
    private function getFromCache($type)
    {
        if (false === $this->hasCache()) {
            return null;
        }
        return $this->cache->loadMetadataFromCache($type);
    }
}

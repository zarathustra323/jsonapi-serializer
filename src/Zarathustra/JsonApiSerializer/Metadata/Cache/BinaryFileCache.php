<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Cache;

use Zarathustra\JsonApiSerializer\Metadata\EntityMetadata;
use Zarathustra\JsonApiSerializer\Exception\RuntimeException;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;
use Zarathustra\JsonApiSerializer\Utility;

/**
 * Caches and retrieves EntityMetadata objects from the file system using igbinary serialization.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class BinaryFileCache extends FileCache
{
    /**
     * The cache type file prefix.
     *
     * @var string
     */
    protected $cachePrefix = 'BinaryCache';

    /**
     * {@inheritDoc}
     */
    protected function readFile($file)
    {
        return igbinary_unserialize(file_get_contents($file));
    }

    /**
     * {@inheritDoc}
     */
    public function putMetadataInCache(EntityMetadata $metadata)
    {
        $this->writeFile($metadata, igbinary_serialize($metadata));
        return $this;
    }
}

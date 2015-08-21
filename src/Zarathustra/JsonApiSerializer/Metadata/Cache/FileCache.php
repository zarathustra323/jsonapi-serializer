<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Cache;

use Zarathustra\JsonApiSerializer\Metadata\EntityMetadata;
use Zarathustra\JsonApiSerializer\Metadata\Formatter\EntityFormatter;
use Zarathustra\JsonApiSerializer\Exception\RuntimeException;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

/**
 * Caches and retrieves EntityMetadata objects from the file system.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class FileCache implements CacheInterface
{
    /**
     * The cache directory.
     *
     * @var string
     */
    protected $dir;

    /**
     * The cache type file prefix.
     *
     * @var string
     */
    protected $cachePrefix = 'FileCache';

    /**
     * The entity formatter utility.
     *
     * @var EntityFormatter
     */
    protected $entityFormatter;

    /**
     * The file extension.
     *
     * @var string
     */
    protected $extension = 'php';

    /**
     * Constructor.
     *
     * @param   string          $dir
     * @param   EntityFormatter $entityFormatter
     */
    public function __construct($dir, EntityFormatter $entityFormatter)
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException(sprintf('The cache directory "%s" does not exist.', $dir));
        }
        if (!is_writable($dir)) {
            throw new InvalidArgumentException(sprintf('The cache directory "%s" is not writable.', $dir));
        }
        $this->dir = rtrim($dir, '\\/');
        $this->entityFormatter = $entityFormatter;
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataFromCache($type)
    {
        $file = $this->getCacheFile($type);
        if (!file_exists($file)) {
            return null;
        }
        return $this->readFile($file);
    }

    /**
     * Reads the cache file and returns as an EntityMetadata object.
     *
     * @param   string  $file
     * @return  EntityMetadata
     */
    protected function readFile($file)
    {
        return include $file;
    }

    /**
     * {@inheritDoc}
     */
    public function putMetadataInCache(EntityMetadata $metadata)
    {
        $this->writeFile($metadata, '<?php return unserialize('.var_export(serialize($metadata), true).');');
        return $this;
    }

    /**
     * Writes the cache file.
     *
     * @param   EntityMetadata  $metadata
     * @param   string          $contents
     */
    protected function writeFile(EntityMetadata $metadata, $contents)
    {
        $file = $this->getCacheFile($metadata->type);
        $tmpFile = tempnam($this->dir, 'metadata-cache');
        file_put_contents($tmpFile, $contents);
        chmod($tmpFile, 0666 & ~umask());
        $this->renameFile($tmpFile, $file);
    }

    /**
     * {@inheritDoc}
     */
    public function evictMetadataFromCache(EntityMetadata $metadata)
    {
        $file = $this->getCacheFile($metadata->type);
        if (file_exists($file)) {
            return unlink($file);
        }
        return false;
    }

    /**
     * Gets the cache file from the entity type.
     *
     * @param   string  $type
     * @return  string
     */
    private function getCacheFile($type)
    {
        return $this->dir.'/JsonApi.'.$this->cachePrefix.'.'.$this->entityFormatter->getFilename($type).'.'.$this->extension;
    }

    /**
     * Renames a file
     *
     * @param  string $source
     * @param  string $target
     * @throws \RuntimeException
     */
    private function renameFile($source, $target)
    {
        if (false === @rename($source, $target)) {
            throw new RuntimeException(sprintf('Could not write new cache file to %s.', $target));
        }
    }
}

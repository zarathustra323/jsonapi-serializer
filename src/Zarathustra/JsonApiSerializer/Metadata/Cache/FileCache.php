<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Cache;

use Zarathustra\JsonApiSerializer\Metadata\EntityMetadata;
use Zarathustra\JsonApiSerializer\Exception\RuntimeException;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;
use Zarathustra\JsonApiSerializer\Utility;

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
    private $dir;

    /**
     * Constructor.
     *
     * @param   string  $dir
     */
    public function __construct($dir)
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException(sprintf('The cache directory "%s" does not exist.', $dir));
        }
        if (!is_writable($dir)) {
            throw new InvalidArgumentException(sprintf('The cache directory "%s" is not writable.', $dir));
        }
        $this->dir = rtrim($dir, '\\/');
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
        return include $file;
    }

    /**
     * {@inheritDoc}
     */
    public function putMetadataInCache(EntityMetadata $metadata)
    {
        $file = $this->getCacheFile($metadata->type);
        $tmpFile = tempnam($this->dir, 'metadata-cache');
        file_put_contents($tmpFile, '<?php return unserialize('.var_export(serialize($metadata), true).');');
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
        return $this->dir.'/json-api.file-cache.'.Utility::formatEntityTypeFilename($type).'.php';
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

<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Driver;

use Zarathustra\JsonApiSerializer\Utility;

/**
 * File locator service for locating metadata files for use in creating EntityMetadata instances.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class FileLocator implements FileLocatorInterface
{
    /**
     * Directories to search in.
     *
     * @var array
     */
    private $directories = [];

    /**
     * Constructor.
     *
     * @param   string|array   $directories
     */
    public function __construct($directories)
    {
        $this->directories = (Array) $directories;
    }

    /**
     * Gets the directories to search in.
     *
     * @return  array
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    /**
     * {@inheritDoc}
     */
    public function findFileForType($type, $extension)
    {
        $type = $this->formatFilename($type);
        foreach ($this->getDirectories() as $prefix => $dir) {
            $path = sprintf('%s/%s', $dir, $this->getFilenameForType($type, $extension, $prefix));
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function findAllTypes($extension)
    {
        $types = [];
        $extension = sprintf('.%s', $extension);

        foreach ($this->getDirectories() as $prefix => $dir) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            $prefix = sprintf('%s.', $this->formatPrefix($prefix));
            foreach ($iterator as $file) {
                if (($fileName = $file->getBasename($extension)) == $file->getBasename()) {
                    continue;
                }
                if (0 !== strpos($fileName, $prefix)) {
                    continue;
                }
                $type = str_replace([$prefix, $extension], '', $fileName);
                $types[] = str_replace('.', '/', $type);
            }
        }
        return $types;
    }

    /**
     * Gets the filename for a metadata entity type.
     *
     * @param   string      $type
     * @param   string      $extension
     * @param   string|null $prefix
     * @return  string
     */
    public function getFilenameForType($type, $extension, $prefix = null)
    {
        return sprintf('%s.%s.%s', $this->formatPrefix($prefix), $type, $extension);
    }

    /**
     * Formats the file name.
     *
     * @param   string  $type
     * @return  string
     */
    private function formatFilename($type)
    {
        return Utility::formatEntityTypeFilename($type);
    }

    /**
     * Formats the file name prefix.
     *
     * @param   string  $prefix
     * @return  string
     */
    private function formatPrefix($prefix)
    {
        return is_string($prefix) && !empty($prefix) ? sprintf('%s', $this->formatFilename($prefix)) : 'json-api';
    }
}

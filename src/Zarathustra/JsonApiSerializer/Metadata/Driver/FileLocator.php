<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Driver;

use Zarathustra\JsonApiSerializer\Metadata\Formatter\EntityFormatter;

/**
 * File locator service for locating metadata files for use in creating EntityMetadata instances.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class FileLocator implements FileLocatorInterface
{
    /**
     * Directory to search in.
     *
     * @var string
     */
    private $dir;

    /**
     * The entity formatter utility.
     *
     * @var EntityFormatter
     */
    private $entityFormatter;

    /**
     * Constructor.
     *
     * @param   string          $dir
     * @param   EntityFormatter $entityFormatter
     */
    public function __construct($dir, EntityFormatter $entityFormatter)
    {
        $this->dir = $dir;
        $this->entityFormatter = $entityFormatter;
    }

    /**
     * Gets the directory to search in.
     *
     * @return  array
     */
    public function getDirectory()
    {
        return $this->dir;
    }

    /**
     * {@inheritDoc}
     */
    public function findFileForType($type, $extension)
    {
        $path = sprintf('%s/%s', $this->getDirectory(), $this->getFilenameForType($type, $extension));
        if (file_exists($path)) {
            return $path;
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

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->getDirectory()),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            if (($filename = $file->getBasename($extension)) == $file->getBasename()) {
                continue;
            }
            $types[] = $this->getTypeForFilename($filename, $extension);
        }
        return $types;
    }

    /**
     * Gets the type name based on filename.
     *
     * @param   string  $filename
     * @param   string  $extension
     * @return  string
     */
    public function getTypeForFilename($filename, $extension)
    {
        $type = str_replace($extension, '', $filename);
        return str_replace('_', '\\', $filename);
    }

    /**
     * Gets the filename for a metadata entity type.
     *
     * @param   string  $type
     * @param   string  $extension
     * @return  string
     */
    public function getFilenameForType($type, $extension)
    {
        return sprintf('%s.%s', $this->entityFormatter->getFilename($type), $extension);
    }
}

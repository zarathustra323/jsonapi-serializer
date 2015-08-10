<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Driver;

use Zarathustra\JsonApiSerializer\Metadata\EntityMetadata;

/**
 * Abstract metadata file driver.
 *
 * @author Jacob Bare <jacob.bare@southcomm.com>
 */
abstract class AbstractFileDriver implements DriverInterface
{
    /**
     * The file locator for locating metadata files.
     *
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * Array cache for in-memory loaded metadata objects.
     *
     * @var EntityMetadata[]
     */
    private $arrayCache = [];

    /**
     * Constructor.
     *
     * @param   FileLocatorInterface    $fileLocator
     */
    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForType($type)
    {
        if (null === $path = $this->fileLocator->findFileForType($type, $this->getExtension())) {
            return null;
        }

        if (isset($this->arrayCache[$type])) {
            return $this->arrayCache[$type];
        }
        return $this->arrayCache[$type] = $this->loadMetadataFromFile($type, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllTypeNames()
    {
        return $this->fileLocator->findAllTypes($this->getExtension());
    }

    /**
     * Reads the content of the file and loads it as an EntityMetadata instance.
     *
     * @param string    $type
     * @param string    $path
     *
     * @return  \Zarathustra\JsonApiSerializer\Metadata\EntityMetadata|null
     */
    abstract protected function loadMetadataFromFile($type, $path);

    /**
     * Returns the extension of the file.
     *
     * @return string
     */
    abstract protected function getExtension();
}

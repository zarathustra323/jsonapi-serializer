<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Driver;

use Zarathustra\JsonApiSerializer\Metadata\EntityMetadata;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

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
        if (isset($this->arrayCache[$type])) {
            return $this->arrayCache[$type];
        }
        $path = $this->getFilePathForType($type);
        return $this->arrayCache[$type] = $this->loadMetadataFromFile($type, $path);
    }

    /**
     * Returns the file path for an entity type.
     *
     * @param   string  $type
     * @return  string
     * @throws  InvalidArgumentException
     */
    protected function getFilePathForType($type)
    {
        if (null === $path = $this->fileLocator->findFileForType($type, $this->getExtension())) {
            throw new InvalidArgumentException(sprintf('Unable to locate a metadata mapping definition for entity type "%s"', $type));
        }
        return $path;
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

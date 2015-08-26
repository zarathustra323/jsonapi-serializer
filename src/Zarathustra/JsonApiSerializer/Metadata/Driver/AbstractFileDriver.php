<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Driver;

use Zarathustra\JsonApiSerializer\DataTypes\TypeFactory;
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
     * The attribute data type factory.
     * Is used to ensure data types are registered.
     *
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * Constructor.
     *
     * @param   FileLocatorInterface    $fileLocator
     */
    public function __construct(FileLocatorInterface $fileLocator, TypeFactory $typeFactory)
    {
        $this->fileLocator = $fileLocator;
        $this->typeFactory = $typeFactory;
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
     * Validates that the given attribute data type exists.
     *
     * @param   string  $dataType
     * @return  bool
     * @throws  InvalidArgumentException
     */
    protected function validateDataType($dataType)
    {
        if (false === $this->typeFactory->hasType($dataType)) {
            throw new InvalidArgumentException(sprintf('The data type "%s" does not exist.', $dataType));
        }
        return true;
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

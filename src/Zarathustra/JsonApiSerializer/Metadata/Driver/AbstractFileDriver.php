<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Driver;

use Zarathustra\JsonApiSerializer\Validator;
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
     * Array cache of all entity types.
     *
     * @var array
     */
    private $allEntityTypes;

    /**
     * Validator component for ensuring formats are correct.
     *
     * @var Validator
     */
    protected $validator;

    /**
     * Constructor.
     *
     * @param   FileLocatorInterface    $fileLocator
     * @param   Validator               $validator
     */
    public function __construct(FileLocatorInterface $fileLocator, Validator $validator)
    {
        $this->fileLocator = $fileLocator;
        $this->validator = $validator;
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
        if (null === $this->allEntityTypes) {
            $this->allEntityTypes = $this->fileLocator->findAllTypes($this->getExtension());
        }
        return $this->allEntityTypes;
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

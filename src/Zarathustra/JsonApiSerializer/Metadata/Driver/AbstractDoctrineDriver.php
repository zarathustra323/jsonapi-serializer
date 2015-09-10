<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Driver;

use Zarathustra\JsonApiSerializer\Validator;
use Zarathustra\JsonApiSerializer\Metadata\Formatter\EntityFormatter;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zarathustra\JsonApiSerializer\Exception\MetadataException;

/**
 * The abstract Doctrine metadata driver.
 *
 * @author Jacob Bare <jacob.bare@southcomm.com>
 */
abstract class AbstractDoctrineDriver implements DriverInterface
{
    /**
     * A Doctrine class metadata factory implementation.
     *
     * @var ClassMetadataFactory
     */
    protected $mf;

    /**
     * The entity metadata formatter.
     *
     * @var EntityFormatter
     */
    protected $formatter;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * Root namespace that all Doctrine class names share.
     * Is used to convert class names to entity types, and vice versa.
     *
     * @param string|null
     */
    protected $rootNamespace;

    /**
     * Array cache of all entity types.
     *
     * @var null|array
     */
    protected $allEntityTypes;

    /**
     * Constructor.
     *
     * @param   ClassMetadataFactory    $mf
     */
    public function __construct(ClassMetadataFactory $mf, EntityFormatter $formatter, Validator $validator, $rootNamespace = null)
    {
        $this->mf = $mf;
        $this->formatter = $formatter;
        $this->validator = $validator;
        $this->rootNamespace = $rootNamespace;
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForType($type)
    {
        return $this->loadFromClassMetadata($this->doLoadClassMetadata($type));
    }

    /**
     * Loads Doctrine ClassMetadata for an API entity type.
     *
     * @param   string  $type
     * @return  ClassMetadata
     * @throws  MetadataException   If the Doctrine ClassMetadata does not exist.
     */
    protected function doLoadClassMetadata($type)
    {
        if (false === $this->classMetadataExists($type)) {
            throw MetadataException::mappingNotFound($type);
        }

        $className = $this->getClassNameForType($type);
        return $this->mf->getMetadataFor($className);
    }

    /**
     * Determines if Doctrine ClassMetadata exists for an API entity type.
     *
     * @param   string  $type
     * @return  bool
     */
    protected function classMetadataExists($type)
    {
        $className = $this->getClassNameForType($type);
        try {
            $metadata = $this->mf->getMetadataFor($className);
            return true;
        } catch (MappingException $e) {
            return false;
        }
        return false === $this->shouldFilterClassMetadata($metadata);
    }

    /**
     * Loads an API entity metadata object from Doctrine ClassMetadata.
     *
     * @abstract
     * @param   ClassMetadata   $metadata
     * @return  \Zarathustra\JsonApiSerializer\Metadata\EntityMetadata
     */
    abstract protected function loadFromClassMetadata(ClassMetadata $metadata);

    /**
     * {@inheritDoc}
     */
    public function getAllTypeNames()
    {
        if (null === $this->allEntityTypes) {
            $this->allEntityTypes = [];
            foreach ($this->mf->getAllMetadata() as $metadata) {
                if (true === $this->shouldFilterClassMetadata($metadata)) {
                    // Do not include filtered metadata.
                    continue;
                }
                $this->allEntityTypes[] = $this->getTypeForClassName($metadata->getName());
            }
        }
        return $this->allEntityTypes;
    }

    /**
     * {@inheritDoc}
     */
    abstract public function getTypeHierarchy($type, array $types = []);

    /**
     * Determines if a Doctrine ClassMetadata instance should be filtered (not included).
     * Must be extended to take effect, per Doctrine driver type.
     *
     * @param   ClassMetadata   $metadata
     * @return  bool
     */
    protected function shouldFilterClassMetadata(ClassMetadata $metadata)
    {
        return false;
    }

    /**
     * Gets the API entity type from a Doctrine class name.
     *
     * @param   string  $className
     * @return  string
     */
    protected function getTypeForClassName($className)
    {
        if (empty($this->rootNamespace)) {
            return $className;
        }
        return $this->stripNamespace($this->rootNamespace, $className);
    }

    protected function stripNamespace($namespace, $toStrip)
    {
        return trim(str_replace($namespace, '', $toStrip), '\\');
    }

    /**
     * Gets the Doctrine class name from an API entity type.
     *
     * @param   string  $type
     * @return  string
     */
    protected function getClassNameForType($type)
    {
        if (!empty($this->rootNamespace) && strstr($type, $this->rootNamespace)) {
            $type = $this->stripNamespace($this->rootNamespace, $type);
        }
        $type = $this->formatter->formatInternalEntityType($type);
        if (!empty($this->rootNamespace)) {
            return sprintf('%s\\%s', $this->rootNamespace, $type);
        }
        return $type;
    }
}

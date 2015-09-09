<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Driver;

use Zarathustra\JsonApiSerializer\Metadata;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;

/**
 * The Doctrine MongoDB metadata driver.
 *
 * @author Jacob Bare <jacob.bare@southcomm.com>
 */
class DoctrineMongoDBDriver extends AbstractDoctrineDriver
{
    /**
     * {@inheritDoc}
     */
    protected function loadFromClassMetadata(ClassMetadata $metadata)
    {
        $type = $this->getTypeForClassName($metadata->getName());

        $entity = new Metadata\EntityMetadata($type);

        $reflClass = $metadata->getReflectionClass();

        $entity->setAbstract($reflClass->isAbstract());

        if (false !== $parent = $reflClass->getParentClass()) {
            $entity->extends = $this->getTypeForClassName($parent->getName());
        }

        if ($this->isPolymorphicType($metadata)) {
            $entity->setPolymorphic(true);
        }

        $this->setAttributes($metadata, $entity);
        $this->setRelationships($metadata, $entity);
        return $entity;
    }

    private function setAttributes(ClassMetadataInfo $metadata, Metadata\AttributeInterface $entity)
    {
        foreach ($metadata->fieldMappings as $fieldKey => $mapping) {
            if (isset($mapping['id'])) {
                // Id field. Skip.
                continue;
            }
            if (isset($mapping['reference'])) {
                // Relationship. Skip.
                continue;
            }
            if (isset($mapping['inherited']) || isset($mapping['declared'])) {
                // Inherited. Skip.
                continue;
            }
            if (!isset($mapping['type'])) {
                // Unable to map. No type.
                throw new InvalidArgumentException(sprintf('Cannot create an attribute for field "%s" because no data type was found', $fieldKey));
            }

            $apiDataType = isset($mapping['embedded']) ? 'object' : $this->getDataType($mapping['type']);
            $this->validator->validateDataType($apiDataType);

            switch ($apiDataType) {
                case 'object':
                    $attribute = new Metadata\ObjectAttributeMetadata($fieldKey, 'object');
                    // @todo This needs some work. How do we determine the child attributes of polymorphic embedded documents?
                    if (isset($mapping['embedded']) && isset($mapping['targetDocument'])) {
                        $childMetadata = $this->mf->getMetadataFor($mapping['targetDocument']);
                        if (false === $this->isPolymorphicType($childMetadata)) {
                            $this->setAttributes($childMetadata, $attribute);
                        }
                    }
                    break;
                case 'array':
                    $attribute = new Metadata\ArrayAttributeMetadata($fieldKey, 'array', 'mixed');
                    break;
                default:
                    $attribute = new Metadata\AttributeMetadata($fieldKey, $apiDataType);
                    break;
            }
            $entity->addAttribute($attribute);
        }
        return $entity;
    }

    private function setRelationships(ClassMetadataInfo $metadata, Metadata\EntityMetadata $entity)
    {
        $allTypes = $this->getAllTypeNames();
        foreach ($metadata->fieldMappings as $fieldKey => $mapping) {
            if (!isset($mapping['reference'])) {
                // Not a relationship. Skip.
                continue;
            }
            if (!isset($mapping['targetDocument'])) {
                // No target found. Skip.
                // @todo Should this throw an Exception?
                continue;
            }

            $type = $this->getTypeForClassName($mapping['targetDocument']);
            if (!in_array($type, $allTypes)) {
                throw new InvalidArgumentException(sprintf('No metadata was found for related entity type "%s" as found on relationship field "%s::%s"', $type, $entity->type, $fieldKey));
            }

            $relationship = new Metadata\RelationshipMetadata($fieldKey, $mapping['type'], $type);
            if (isset($mapping['isInverseSide']) && true === $mapping['isInverseSide']) {
                $relationship->isInverse = true;
            }
            $entity->addRelationship($relationship);
        }
        return $entity;
    }

    private function getDataType($doctrineType)
    {
        $map = [
            'string'    => 'string',
            'date'      => 'date',
            'int'       => 'integer',
            'boolean'   => 'boolean',
            'collection'=> 'array',
            'hash'      => 'object',
            'float'     => 'float',
            'raw'       => 'mixed',
            'object_id' => 'string',
            'timestamp' => 'date',
            'custom_id' => 'string',
            'file'      => 'mixed',
            'increment' => 'integer',
        ];
        if (!isset($map[$doctrineType])) {
            throw new InvalidArgumentException(sprintf('The Doctrine type "%s" is currenty not implemented by the API.', $doctrineType));
        }
        return $map[$doctrineType];
    }

    private function isPolymorphicType(ClassMetadataInfo $metadata)
    {
        return in_array($metadata->inheritanceType, $this->getPolymorphicTypes()) && null === $metadata->discriminatorValue;
    }

    private function getPolymorphicTypes()
    {
        return [ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_COLLECTION, ClassMetadataInfo::INHERITANCE_TYPE_COLLECTION_PER_CLASS];
    }

    /**
     * {@inheritDoc}
     */
    protected function shouldFilterClassMetadata(ClassMetadata $metadata)
    {
        return true === $metadata->isEmbeddedDocument;
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeHierarchy($type, array $types = [])
    {
        $metadata = $this->doLoadClassMetadata($type);
        $reflClass = $metadata->getReflectionClass();

        $types[] = $this->getTypeForClassName($reflClass->getName());
        if (false === $reflClass->getParentClass()) {
            return array_reverse($types);
        }
        return $this->getTypeHierarchy($reflClass->getParentClass()->getName(), $types);
    }
}

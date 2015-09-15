<?php

namespace Zarathustra\ModlrData\Resource;

use Zarathustra\ModlrData\Metadata\MetadataFactory;
use Zarathustra\ModlrData\Exception\InvalidArgumentException;

/**
 * Factory for creating and building a resource object structure, for use in API adapters and persistence stores.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class ResourceFactory
{
    /**
     * @var MetadataFactory
     */
    private $mf;

    /**
     * Constructor.
     *
     * @param   MetadataFactory   $mf
     */
    public function __construct(MetadataFactory $mf)
    {
        $this->mf = $mf;
    }

    /**
     * Creates a new resource.
     *
     * @param   string  $entityType     The primary entity type this resource represents.
     * @param   string  $resourceType   The resource type: one or many.
     * @return  Document
     */
    public function createResource($entityType, $resourceType)
    {
        return new Resource($entityType, $resourceType);
    }

    /**
     * Creates a new entity.
     *
     * @param   string  $type   The entity type.
     * @param   string  $id     The entity unique identifier.
     * @return  Entity
     */
    public function createEntity($type, $id)
    {
        return new Entity($id, $type);
    }

    /**
     * Creates a new entity identifier.
     *
     * @param   string  $type   The entity type.
     * @param   string  $id     The entity unique identifier.
     * @return  Identifier
     */
    public function createEntityIdentifier($type, $id)
    {
        return new Identifier($id, $type);
    }

    /**
     * Applies an entity or entity identifier to a resource.
     *
     * @param   Resource            $document   The primary resource.
     * @param   EntityInterface     $entity     The entity or entity identifier to add.
     * @return  self
     */
    public function applyEntity(Resource $resource, EntityInterface $entity)
    {
        $this->validateResourceTypes($resource->getEntityType(), $entity->getType());
        $document->pushData($entity);
        return $this;
    }

    /**
     * Applies an array or array-like set of relationship data to an entity.
     * Each array member must be a EntityInterface object keyed by the relationship field key.
     *
     * @param   Entity              $owner      The owning entity.
     * @param   array|\ArrayAccess  $data       The resource data to apply.
     * @return  self
     */
    public function applyRelationships(Entity $owner, $data)
    {
        $this->validateData($data);
        $meta = $this->mf->getMetadataForType($owner->getType());
        foreach ($meta->getRelationships() as $key => $relationship) {
            if (!isset($data[$key]) || !$data[$key] instanceof EntityInterface) {
                continue;
            }
            $this->applyRelationship($owner, $key, $data[$key]);
        }
        return $this;
    }

    /**
     * Applies a single relationship to an owning resource via a related resource object.
     *
     * @param   Entity              $owner          The owning entity to apply the relationship to.
     * @param   string              $fieldKey       The relationship field key.
     * @param   EntityInterface     $related        The related EntityInterface to add to the owner.
     * @throws  InvalidArgumentException            If the relationship field key does not exist on the owner.
     * @return  self
     */
    public function applyRelationship(Entity $owner, $fieldKey, EntityInterface $related)
    {
        $meta = $this->em->getMetadataForType($owner->getType());
        if (false === $meta->hasRelationship($fieldKey)) {
            throw new InvalidArgumentException('The resource "%s" does not contain relationship field "%s"', $owner->getType(), $fieldKey);
        }
        $relMeta = $meta->getRelationship($fieldKey);
        $this->validateResourceTypes($relMeta->getEntityType(), $related->getType());

        $relationship = $this->createRelationship($fieldKey, $relMeta->getType());
        $relationship->pushData($related);
        $owner->addRelationship($relationship);
        return $this;
    }

    /**
     * Applies an array or array-like set of attribute data to an entity.
     * Each array member must be keyed by the attribute field key.
     *
     * @param   Entity              $entity     The entity to apply the attributes to.
     * @param   array|\ArrayAccess  $data       The attribute data to apply.
     * @return  self
     */
    public function applyAttributes(Resource $entity, $data)
    {
        $this->validateData($data);
        $meta = $this->em->getMetadataFor($entity->getType());
        foreach ($meta->getAttributes() as $key => $attribute) {
            if (!isset($data[$key])) {
                continue;
            }
            $this->applyAttribute($entity, $key, $data[$key]);
        }
        return $this;
    }

    /**
     * Applies a single attribute value to a resource.
     *
     * @param   Entity      $entity     The entity to apply the attribute value to.
     * @param   string      $fieldKey   The attribute field key.
     * @param   mixed       $value      The attribute value.
     * @return  self
     */

    public function applyAttribute(Entity $entity, $fieldKey, $value)
    {
        $entity->addAttribute($this->createAttribute($fieldKey, $value));
        return $this;
    }

    /**
     * Validates that a data set is an array or is array-like.
     *
     * @param   array|\ArrayAccess  $data
     * @throws  InvalidArgumentException    If the data is not of the proper type.
     * @return  bool
     */
    protected function validateData($data)
    {
        if (false === is_array($data) && !$data instanceof \ArrayAccess) {
            throw new InvalidArgumentException('Data must be accessible as an array.');
        }
        return true;
    }

    /**
     * Validates that an related or child entity type is compatible with a parent or owning type.
     * Ensures that collections and relationships only contain types that have been defined.
     *
     * @param   string  $parentType
     * @param   string  $childType
     * @throws  InvalidArgumentException    The child type is not a descendant of a polymorphic parent, or the types are not identical.
     * @return  bool
     */
    protected function validateResourceTypes($parentType, $childType)
    {
        $meta = $this->mf->getMetadataForType($parentType);
        if (true === $meta->isPolymorphic() && false === $this->mf->isDescendantOf($childType, $parentType)) {
            throw new InvalidArgumentException(sprintf('The resource type "%s" is polymorphic. Resource "%s" is not a descendent of "%s"', $parentType, $childType, $parentType));
        } elseif (false === $meta->isPolymorphic() && $parentType !== $childType) {
            throw new InvalidArgumentException(sprintf('This resource only supports resources of type "%s" - resource type "%s" was provided', $parentType, $childType));
        }
        return true;
    }

    /**
     * Creates a new attribute document.
     *
     * @param   string  $key
     * @param   mixed   $value
     * @return  Attribute
     */
    protected function createAttribute($key, $value)
    {
        return new Attribute($key, $value);
    }

    /**
     * Creates a new relationship document.
     *
     * @param   string  $key
     * @param   string  $relType    The relationship type: one or many.
     * @return  Relationship
     */
    protected function createRelationship($key, $relType)
    {
        return new Relationship($key, $relType);
    }
}

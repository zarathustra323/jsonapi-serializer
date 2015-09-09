<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure;

use Zarathustra\JsonApiSerializer\EntityManager;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

/**
 * Factory for creating and managing an API document structure, for use in serialization.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class DocumentFactory
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param   EntityManager   $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Creates a new document.
     *
     * @param   string  $entityType The primary entity type this document represents.
     * @param   string  $docType    The document type: one or many.
     * @return  Document
     */
    public function createDocument($entityType, $docType)
    {
        return new Document($entityType, $docType);
    }

    /**
     * Creates a new resource.
     *
     * @param   string  $type   The entity type.
     * @param   string  $id     The resource unique identifier.
     * @return  Resource
     */
    public function createResource($type, $id)
    {
        return new Resource($id, $type);
    }

    /**
     * Applies a resource to a document.
     *
     * @param   Document    $document   The primary document.
     * @param   Resource    $resource   The resource to add.
     * @return  self
     */
    public function applyResource(Document $document, Resource $resource)
    {
        $this->validateResourceTypes($document->getEntityType(), $resource->getType());
        $document->pushData($resource);
        return $this;
    }

    /**
     * Applies an array or array-like set of relationship data to a resource.
     * Each array member must be a Resource object keyed by the relationship field key.
     *
     * @param   Resource            $owner      The owning resource.
     * @param   array|\ArrayAccess  $data       The resource data to apply.
     * @return  self
     */
    public function applyRelationships(Resource $owner, $data)
    {
        $this->validateData($data);
        $meta = $this->em->getMetadataFor($owner->getType());
        foreach ($meta->getRelationships() as $key => $relationship) {
            if (!isset($data[$key]) || !$data[$key] instanceof Resource) {
                continue;
            }
            $this->applyRelationship($owner, $key, $data[$key]);
        }
        return $this;
    }

    /**
     * Applies a single relationship to an owning resource via a related resource object.
     *
     * @param   Resource    $owner          The owning resource to apply the relationship to.
     * @param   string      $fieldKey       The relationship field key.
     * @param   Resource    $related        The related resource to add to the owner.
     * @throws  InvalidArgumentException    If the relationship field key does not exist on the owner.
     * @return  self
     */
    public function applyRelationship(Resource $owner, $fieldKey, Resource $related)
    {
        $meta = $this->em->getMetadataFor($owner->getType());
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
     * Applies an array or array-like set of attribute data to a resource.
     * Each array member must be keyed by the attribute field key.
     *
     * @param   Resource            $resource   The resource to apply the attributes to.
     * @param   array|\ArrayAccess  $data       The attribute data to apply.
     * @return  self
     */
    public function applyAttributes(Resource $resource, $data)
    {
        $this->validateData($data);
        $meta = $this->em->getMetadataFor($resource->getType());
        foreach ($meta->getAttributes() as $key => $attribute) {
            if (!isset($data[$key])) {
                continue;
            }
            $this->applyAttribute($resource, $key, $data[$key]);
        }
        return $this;
    }

    /**
     * Applies a single attribute value to a resource.
     *
     * @param   Resource    $resource   The resource to apply the attribute value to.
     * @param   string      $fieldKey   The attribute field key.
     * @param   mixed       $value      The attribute value.
     * @return  self
     */

    public function applyAttribute(Resource $resource, $fieldKey, $value)
    {
        $resource->addAttribute($this->createAttribute($fieldKey, $value));
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
        $meta = $this->em->getMetadataFor($parentType);
        if (true === $meta->isPolymorphic() && false === $this->em->isDescendantOf($childType, $parentType)) {
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

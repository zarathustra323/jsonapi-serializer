<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure;

use Zarathustra\JsonApiSerializer\EntityManager;
use Zarathustra\JsonApiSerializer\Exception\InvalidArgumentException;

class DocumentFactory
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function createDocument($entityType, $docType)
    {
        return new Document($entityType, $docType);
    }

    public function pushResource(Document $document, Resource $resource)
    {
        $this->validateResourceTypes($document->getEntityType(), $resource->getType());
        $document->pushData($resource);
    }

    public function pushRelationship(Resource $owner, $fieldKey, Resource $related)
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
    }

    protected function validateResourceTypes($parentType, $childType)
    {
        $meta = $this->em->getMetadataFor($parentType);
        if (true === $meta->isPolymorphic() && false === $this->em->isDescendantOf($childType, $parentType)) {
            throw new InvalidArgumentException(sprintf('The resource type "%s" is polymorphic. Resource "%s" is not a descendent of "%s"', $parentType, $childType, $parentType));
        } elseif (false === $meta->isPolymorphic() && $parentType !== $childType) {
            throw new InvalidArgumentException(sprintf('This resource only supports resources of type "%s" - resource type "%s" was provided', $parentType, $childType));
        }
    }

    public function applyResourceAttributes(Resource $resource, $data)
    {
        if (false === is_array($data) && !$data instanceof \ArrayAccess) {
            throw new InvalidArgumentException('Data must be accessible as an array.');
        }
        $meta = $this->em->getMetadataFor($resource->getType());
        foreach ($meta->getAttributes() as $key => $attribute) {
            if (!isset($data[$key])) {
                continue;
            }
            $this->createResourceAttribute($resource, $key, $data[$key]);
        }
    }

    public function createResource($type, $id)
    {
        return new Resource($id, $type);
    }

    public function createAttribute($key, $value)
    {
        return new Attribute($key, $value);
    }

    public function createRelationship($key, $relType)
    {
        return new Relationship($key, $relType);
    }

    public function createResourceAttribute(Resource $resource, $fieldKey, $value)
    {
        $resource->addAttribute($this->createAttribute($fieldKey, $value));
        return $this;
    }

    public function createResourceRelationship(Resource $resource, $fieldKey, RelatedDataInterface $data = null)
    {
        $resource->addRelationship($this->createRelationship($fieldKey, $data));
        return $this;
    }

    public function addResourceToCollection(ResourceCollection $collection, Resource $resource)
    {
        $collection[] = $resource;
        return $this;
    }

}

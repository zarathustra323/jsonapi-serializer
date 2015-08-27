<?php

namespace Zarathustra\JsonApiSerializer;

use Zarathustra\JsonApiSerializer\DataTypes\TypeFactory;
use Zarathustra\JsonApiSerializer\Exception\RuntimeException;
use Zarathustra\JsonApiSerializer\DocumentStructure\Resource;
use Zarathustra\JsonApiSerializer\DocumentStructure\Attribute;
use Zarathustra\JsonApiSerializer\DocumentStructure\Relationship;
use Zarathustra\JsonApiSerializer\DocumentStructure\ResourceCollection;
use Zarathustra\JsonApiSerializer\Metadata\AttributeMetadata;
use Zarathustra\JsonApiSerializer\Metadata\RelationshipMetadata;

/**
 * Primary serializer class. Responsible for serializing document structure objects into JSON.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class Serializer
{
    /**
     * The Entity Manager.
     *
     * @var EntityManager
     */
    private $em;

    /**
     * The type factory.
     * Used for converting values to the API data type format.
     *
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * The serializer configuration.
     *
     * @var Configuration
     */
    private $config;

    /**
     * Whether debug is enabled.
     * Currently allows Exceptions to be thrown if enabled.
     * Otherwise they are caught and return as a JSON API errors object.
     *
     * @var bool
     */
    private $debug;

    /**
     * Denotes the current object depth of the serializer.
     *
     * @var int
     */
    private $depth = 0;

    /**
     * Constructor.
     *
     * @todo    Configuration needs to be injected to handle things such as global date format, API host/endpoints, resource type conversion, etc.
     *
     * @param   EntityManager   $em
     * @param   TypeFactory     $typeFactory
     * @param   Configuration   $config
     * @param   bool            $debug
     */
    public function __construct(EntityManager $em, TypeFactory $typeFactory, Configuration $config, $debug = false)
    {
        $this->em = $em;
        $this->typeFactory = $typeFactory;
        $this->config = $config;
        $this->debug = (Boolean) $debug;
    }

    /**
     * Serializes the provided document data into JSON API format.
     * Supports either a single Resource object or a ResourceCollection.
     * Can directly encode to a JSON string, or can returned as a formatted array.
     *
     * @todo    Handle meta and included objects.
     * @todo    Handle sorting, pagination, etc - or should this be handled by the query/hydrator classes?
     *
     * @param   Resource|ResourceCollection     $data
     * @param   bool                            $encode
     * @return  string|array
     */
    public function serialize($data, $encode = true)
    {
        $encode = (Boolean) $encode;
        try {
            $serialized = $this->doSerialize($data);
        } catch (\Exception $e) {
            $serialized = $this->handleException($e);
        }
        return $encode ? $this->encode($serialized) : $serialized;
    }

    /**
     * Performs the serialization using the provided document data.
     *
     * @param   Resource|ResourceCollection     $data
     * @return  array
     * @throws  RuntimeException If the provided data is not supported.
     */
    protected function doSerialize($data)
    {
        if ($data instanceof ResourceCollection) {
            $serialized['data'] = $this->serializeCollection($data);
        } elseif ($data instanceof Resource) {
            $serialized['data'] = $this->serializeResource($data);
        } else {
            throw new RuntimeException('Unable to serialize the provided data.');
        }
        return $serialized;
    }

    /**
     * Serializes a single document resource object.
     *
     * @todo    The 'isRelationship' argument should probaby be converted into a 'depth' check.
     *
     * @param   Resource    $resource
     * @param   bool        $isRelationship     Determines if this is serializing a top-level resource, or a related resource.
     * @return  array
     */
    protected function serializeResource(Resource $resource, $isRelationship = false)
    {
        $serialized = [
            'id'    => $resource->getId(),
            'type'  => $resource->getType(),
        ];
        if ($this->depth > 0) {
            return $serialized;
        }

        $metadata = $this->getMetadataFor($resource->getType());
        foreach ($resource->getAttributes() as $key => $attribute) {
            if (false === $metadata->hasAttribute($key)) {
                continue;
            }
            $attrMeta = $metadata->getAttribute($key);
            if (false === $attrMeta->shouldSerialize()) {
                continue;
            }
            $serialized['attributes'][$key] = $this->serializeAttribute($attribute, $attrMeta);
        }

        foreach ($resource->getRelationships() as $key => $relationship) {
            if (false === $metadata->hasRelationship($key)) {
                continue;
            }
            $relMeta = $metadata->getRelationship($key);
            if (false === $relMeta->shouldSerialize()) {
                continue;
            }
            $serialized['relationships'][$key] = $this->serializeRelationship($relationship, $relMeta);
        }
        return $serialized;
    }

    /**
     * Serializes a collection of document resources.
     *
     * @todo    The 'isRelationship' argument should probaby be converted into a 'depth' check.
     *
     * @param   ResourceCollection  $collection
     * @param   bool                $isRelationship     Determines if this is serializing a top-level resource, or a related resource.
     * @return  array
     */
    protected function serializeCollection(ResourceCollection $collection, $isRelationship = false)
    {
        $this->validateCollection($collection);

        $serialized = [];
        foreach ($collection as $resource) {
            $serialized[] = $this->serializeResource($resource, $isRelationship);
        }
        return $serialized;
    }

    /**
     * Serializes an attribute value.
     *
     * @param   Attribute           $attribute
     * @param   AttributeMetadata   $attrMeta
     * @return  mixed
     */
    protected function serializeAttribute(Attribute $attribute, AttributeMetadata $attrMeta)
    {
        if ('object' === $attrMeta->type && $attrMeta->hasAttributes()) {
            $serialized = [];
            $values = get_object_vars($this->typeFactory->convertToPHPValue('object', $attribute->getValue()));
            foreach ($values as $key => $value) {
                if (null === $value) {
                    continue;
                }
                if (false === $attrMeta->hasAttribute($key)) {
                    continue;
                }
                $serialized[$key] = $this->serializeAttribute(new Attribute($key, $value), $attrMeta->getAttribute($key));
            }
            return $serialized;
        }
        return $this->typeFactory->convertToApiValue($attrMeta->type, $attribute->getValue());
    }

    /**
     * Serializes a relationship value
     *
     * @todo    Need support for related links.
     * @todo    Need support for inclided data.
     * @todo    Need support for meta.
     *
     * @param   Relationship            $relationship
     * @param   RelationshipMetadata    $relMeta
     * @return  null|array
     * @throws  RuntimeException        If the relationship data type doesn't match the requirements for the relationship type.
     */
    protected function serializeRelationship(Relationship $relationship, RelationshipMetadata $relMeta)
    {
        if (false === $relationship->hasData()) {
            // No relationship data found, use default value.
            return $relMeta->getDefaultEmptyValue();
        }

        if (true === $relMeta->isOne() && false === $relationship->isResource()) {
            // Invalid. Cannot not serialize a relationship one without a resource object.
            throw new RuntimeException('Cannot serialize a relationship type of "one" without any resource data');
        }
        if (true === $relMeta->isMany() && false === $relationship->isCollection()) {
            // Invalid. Cannot not serialize a relationship many without a collection object.
            throw new RuntimeException('Cannot serialize a relationship type of "many" without any resource collection data');
        }

        $this->increaseDepth();
        $serialized = $this->doSerialize($relationship->getData());
        $this->decreaseDepth();
        return $serialized;
    }

    /**
     * Increases the serializer depth.
     *
     * @return  self
     */
    protected function increaseDepth()
    {
        $this->depth++;
        return $this;
    }

    /**
     * Decreases the serializer depth.
     *
     * @return  self
     */
    protected function decreaseDepth()
    {
        if ($this->depth > 0) {
            $this->depth--;
        }
        return $this;
    }

    /**
     * Encodes the formatted JSON API spec array to a JSON string.
     *
     * @param   array   $data
     * @return  string
     */
    protected function encode(array $data)
    {
        return json_encode($data);
    }

    /**
     * Validates that a resource collection's included resources are valid.
     * Ensures polymorphic collections do not contain resources that aren't a descendant.
     * Ensures that non-polymorphic collections do not contain resources of other entity types.
     *
     * @param   ResourceCollection  $collection
     * @return  bool
     * @throws  RuntimeException
     */
    protected function validateCollection(ResourceCollection $collection)
    {
        $type = $collection->getType();
        $meta = $this->getMetadataFor($type);
        foreach ($collection->getResourceTypes() as $resourceType) {
            if (true === $meta->isPolymorphic() && false === $this->em->isDescendantOf($resourceType, $type)) {
                throw new RuntimeException(sprintf('This resource collection is polymorphic and only descendents of "%s" are supported. Resource "%s" was present in the collection.', $resourceType, $type));
            } elseif ($type !== $resourceType) {
                throw new RuntimeException(sprintf('This resource collection only supports resources of type "%s" - resource type "%s" was provided', $type, $resourceType));
            }
        }
        return true;
    }

    /**
     * Handles serialization exceptions based on the current debug status.
     * Will either throw the exception, or return an error object.
     *
     * @todo    This should fire an event that can be hooked into to support custom error handling.
     * @todo    This needs to more robust to handle different types of Exceptions and support error configuration.
     *
     * @param   \Exception  $e
     * @return  array
     * @throws  \Exception
     */
    protected function handleException(\Exception $e)
    {
        if (true === $this->debug) {
            throw $e;
        }
        return [
            'errors'    => [
                ['title' => 'An internal server error occurred.', 'code' => '500', 'detail' => $e->getMessage()],
            ],
        ];
    }

    /**
     * Gets the entity metadata for the given resource type.
     *
     * @param   string  $type
     * @return  \Zarathustra\JsonApiSerializer\Metadata\EntityMetadata
     */
    protected function getMetadataFor($type)
    {
        return $this->em->getMetadataFor($type);
    }
}

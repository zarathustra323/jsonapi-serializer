<?php

namespace Zarathustra\JsonApiSerializer;

use Zarathustra\JsonApiSerializer\DataTypes\TypeFactory;
use Zarathustra\JsonApiSerializer\Exception\RuntimeException;
use Zarathustra\JsonApiSerializer\DocumentStructure\Document;
use Zarathustra\JsonApiSerializer\DocumentStructure\Resource;
use Zarathustra\JsonApiSerializer\DocumentStructure\Collection;
use Zarathustra\JsonApiSerializer\DocumentStructure\Attribute;
use Zarathustra\JsonApiSerializer\DocumentStructure\Relationship;
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
     * A stack of resources objects to include.
     *
     * @var Resource[]
     */
    private $toInclude = [];

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
     * @param   Document    $document
     * @param   bool        $encode
     * @return  string|array
     */
    public function serialize(Document $document, $encode = true)
    {
        $encode = (Boolean) $encode;
        try {
            $serialized = $this->doSerialize($document->getPrimaryData());
        } catch (\Exception $e) {
            $serialized = $this->handleException($e);
        }
        return $encode ? $this->encode($serialized) : $serialized;
    }

    /**
     * Performs the serialization using the provided document data.
     *
     * @param   Resource|ResourceCollection|null    $data
     * @return  array
     * @throws  RuntimeException If the provided data is not supported.
     */
    protected function doSerialize($data)
    {
        if ($data instanceof Collection) {
            $serialized['data'] = $this->serializeCollection($data);
        } elseif ($data instanceof Resource) {
            $serialized['data'] = $this->serializeResource($data);
        } elseif (null === $data || [] === $data) {
            $serialized['data'] = $data;
        } else {
            throw new RuntimeException('Unable to serialize the provided data.');
        }
        return $this->serializeIncluded($serialized);
    }

    /**
     * Serializes any included resources.
     *
     * @param   array   $serialized
     * @return  array
     */
    protected function serializeIncluded(array $serialized)
    {
        if (0 === $this->depth && !empty($this->toInclude)) {
            foreach ($this->toInclude as $resource) {
                $serialized['included'][] = $this->serializeResource($resource);
            }
            $this->toInclude = [];
        }
        return $serialized;
    }

    /**
     * Serializes a single document resource object.
     *
     * @param   Resource    $resource
     * @return  array
     */
    protected function serializeResource(Resource $resource)
    {
        $metadata = $this->getMetadataFor($resource->getType());

        $serialized = [
            'type'  => $metadata->externalType,
            'id'    => $resource->getId(),
        ];
        if ($this->depth > 0) {
            $this->includeResource($resource);
            return $serialized;
        }

        foreach ($metadata->getAttributes() as $key => $attrMeta) {
            if (false === $attrMeta->shouldSerialize()) {
                continue;
            }
            $attribute = $resource->getAttribute($key);
            $formattedKey = $attrMeta->externalKey;
            $serialized['attributes'][$formattedKey] = $this->serializeAttribute($attribute, $attrMeta);
        }

        $serialized['links'] = ['self' => $this->buildLink($metadata->externalType, $resource->getId())];

        foreach ($metadata->getRelationships() as $key => $relMeta) {
            if (false === $relMeta->shouldSerialize()) {
                continue;
            }
            $relationship = $resource->getRelationship($key);
            $formattedKey = $relMeta->externalKey;
            $serialized['relationships'][$formattedKey] = $this->serializeRelationship($resource, $relationship, $relMeta);
        }
        return $serialized;
    }

    /**
     * Serializes a collection of document resources.
     *
     * @param   Collection  $collection
     * @return  array
     */
    protected function serializeCollection(Collection $collection)
    {
        $serialized = [];
        foreach ($collection as $resource) {
            $serialized[] = $this->serializeResource($resource);
        }
        return $serialized;
    }

    /**
     * Serializes an attribute value.
     *
     * @param   Attribute|null      $attribute
     * @param   AttributeMetadata   $attrMeta
     * @return  mixed
     */
    protected function serializeAttribute(Attribute $attribute = null, AttributeMetadata $attrMeta)
    {
        if (null === $attribute) {
            return $this->typeFactory->convertToApiValue($attrMeta->type, null);
        }
        if ('object' === $attrMeta->type && $attrMeta->hasAttributes()) {
            // If object attributes (sub-attributes) are defined, attempt to convert them to the proper data types.
            $serialized = [];
            $values = get_object_vars($this->typeFactory->convertToPHPValue('object', $attribute->getValue()));
            foreach ($values as $key => $value) {
                if (null === $value) {
                    continue;
                }
                if (false === $attrMeta->hasAttribute($key)) {
                    continue;
                }
                $serialized[$attrMeta->externalKey] = $this->serializeAttribute(new Attribute($key, $value), $attrMeta->getAttribute($key));
            }
            return $serialized;
        }
        return $this->typeFactory->convertToApiValue($attrMeta->type, $attribute->getValue());
    }

    /**
     * Serializes a relationship value
     *
     * @todo    Need support for meta.
     *
     * @param   Resource                $owner
     * @param   Relationship|null       $relationship
     * @param   RelationshipMetadata    $relMeta
     * @return  array
     */
    protected function serializeRelationship(Resource $owner, Relationship $relationship = null, RelationshipMetadata $relMeta)
    {
        if (null === $relationship || false === $relationship->hasData()) {
            // No relationship data found, use default value.
            $data = $relMeta->getDefaultEmptyValue();
        } else {
            $data = $relationship->getData();
        }

        $this->increaseDepth();

        $serialized = $this->doSerialize($data);
        $ownerMeta = $this->getMetadataFor($owner->getType());

        $serialized['links'] = [
            'self'      => $this->buildLink($ownerMeta->externalType, $owner->getId(), $relMeta->externalKey),
            'related'   => $this->buildLink($ownerMeta->externalType, $owner->getId(), $relMeta->externalKey, true),
        ];
        $this->decreaseDepth();
        return $serialized;
    }

    /**
     * Builds a resource URL for use in the links object.
     *
     * @param   string          $externalType
     * @param   string          $id
     * @param   string|null     $externalRelKey
     * @param   bool            $isRelatedLink
     * @return  string
     */
    protected function buildLink($externalType, $id, $externalRelKey = null, $isRelatedLink = false)
    {
        $link = $this->config->isSecure() ? 'https://' : 'http://';
        $link .= $this->config->getApiHost();

        if (null !== $rootEndpoint = $this->config->getRootEndpoint()) {
            $rootEndpoint = trim($rootEndpoint, '/');
            $link .= sprintf('/%s', $rootEndpoint);
        }

        if (true === $this->config->useNamespacesAsResources()) {
            $externalType = str_replace($this->config->getNamespaceDelimiter(), '/', $externalType);
        }
        $link .= sprintf('/%s/%s', $externalType, $id);

        if (null !== $externalRelKey) {
            if (false === $isRelatedLink) {
                $link .= '/relationships';
            }
            $link .= sprintf('/%s', $externalRelKey);
        }
        return $link;
    }

    /**
     * Includes a resource for serialization with compound documents.
     *
     * @param   Resource    $resource
     * @return  self
     */
    protected function includeResource(Resource $resource)
    {
        if ($resource->isCompleteObject()) {
            $this->toInclude[$resource->getCompositeKey()] = $resource;
        }
        return $this;
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

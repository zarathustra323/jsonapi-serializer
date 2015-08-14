<?php

namespace Zarathustra\JsonApiSerializer\Metadata\Driver;

use Zarathustra\JsonApiSerializer\Metadata\EntityMetadata;
use Zarathustra\JsonApiSerializer\Metadata\AttributeMetadata;
use Zarathustra\JsonApiSerializer\Metadata\RelationshipMetadata;
use Zarathustra\JsonApiSerializer\Exception\RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * The YAML metadata file driver.
 *
 * @author Jacob Bare <jacob.bare@southcomm.com>
 */
class YamlFileDriver extends AbstractFileDriver
{
    /**
     * An in-memory cache of parsed metadata mappings (from file).
     *
     * @var array
     */
    private $mappings = [];

    /**
     * {@inheritDoc}
     */
    protected function loadMetadataFromFile($type, $file)
    {
        $mapping = $this->getMapping($type, $file);

        $metadata = new EntityMetadata($type);

        if (isset($mapping['entity']['abstract']) && true === (Boolean) $mapping['entity']['abstract']) {
            $metadata->setAbstract();
        }

        if (isset($mapping['entity']['extends'])) {
            $metadata->extends = $mapping['entity']['extends'];
        }

        $this->setAttributes($metadata, $mapping['attributes']);
        $this->setRelationships($metadata, $mapping['relationships']);
        return $metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeHierarchy($type, array $types = [])
    {
        $path = $this->getFilePathForType($type);
        $mapping = $this->getMapping($type, $path);

        $types[] = $type;
        if (isset($mapping['entity']['extends'])) {
            return $this->getTypeHierarchy($mapping['entity']['extends'], $types);
        }
        return array_reverse($types);
    }

    /**
     * Gets the metadata mapping information from the YAML file.
     *
     * @param   string  $type
     * @param   string  $file
     * @return  array
     * @throws  RuntimeExeption If the file could not be properly parsed.
     */
    private function getMapping($type, $file)
    {
        if (isset($this->mappings[$type])) {
            // Set to array cache to prevent multiple gets/parses.
            return $this->mappings[$type];
        }

        $contents = Yaml::parse(file_get_contents($file));
        if (!isset($contents[$type])) {
            throw new RuntimeException(sprintf('The YAML file must be keyed with the entity type "%s" but was not found.', $type));
        }
        return $this->mappings[$type] = $this->setDefaults($contents[$type]);
    }

    /**
     * Sets the entity attribute metadata from the metadata mapping.
     *
     * @param   EntityMetadata  $metadata
     * @param   array           $attrMapping
     * @return  EntityMetadata
     */
    protected function setAttributes(EntityMetadata $metadata, array $attrMapping)
    {
        foreach ($attrMapping as $key => $mapping) {
            if (!is_array($mapping)) {
                $mapping = ['type' => null];
            }
            $metadata->addAttribute(new AttributeMetadata($key, $mapping['type']));
        }
        return $metadata;
    }

    /**
     * Sets the entity relationship metadata from the metadata mapping.
     *
     * @param   EntityMetadata  $metadata
     * @param   array           $relMapping
     * @return  EntityMetadata
     * @throws  RuntimeException If the related entity type was not found.
     */
    protected function setRelationships(EntityMetadata $metadata, array $relMapping)
    {
        foreach ($relMapping as $key => $mapping) {
            if (!is_array($mapping)) {
                $mapping = ['type' => null, 'entity' => null];
            }
            $relatedMeta = $this->loadMetadataForType($mapping['entity']);

            if (null === $relatedMeta) {
                throw new RuntimeException(sprintf('No YAML mapping file was found for related entity type "%s" as found on relationship field "%s::%s"', $mapping['entity'], $metadata->type, $key));
            }

            $metadata->addRelationship(new RelationshipMetadata($key, $mapping['type'], $relatedMeta));
        }
        return $metadata;
    }

    /**
     * Sets default values to the metadata mapping array.
     *
     * @param   mixed   $mapping
     * @return  array
     */
    protected function setDefaults($mapping)
    {
        if (!is_array($mapping)) {
            $mapping = [];
        }
        foreach (['entity', 'attributes', 'relationships'] as $key) {
            if (!isset($mapping[$key]) || !is_array($mapping[$key])) {
                $mapping[$key] = [];
            }
        }
        return $mapping;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtension()
    {
        return 'yml';
    }
}

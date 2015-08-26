<?php

namespace Zarathustra\JsonApiSerializer;

use Zarathustra\JsonApiSerializer\Metadata\MetadataFactory;

/**
 * Responsible for common entity management methods, helpers, etc.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class EntityManager
{
    /**
     * The Metadata factory.
     *
     * @var MetadataFactory
     */
    private $mf;

    /**
     * Constructor.
     *
     * @param   MetadataFactory     $mf
     */
    public function __construct(MetadataFactory $mf)
    {
        $this->mf = $mf;
    }

    /**
     * Determines if a type is direct child of another type.
     *
     * @param   string  $child
     * @param   string  $parent
     * @return  bool
     */
    public function isChildOf($child, $parent)
    {
        $childMeta = $this->getMetadataFor($child);
        if (false === $childMeta->isChildEntity()) {
            return false;
        }
        return $childMeta->getParentEntityType() === $parent;
    }

    /**
     * Determines if a type is an ancestor of another type.
     *
     * @param   string  $parent
     * @param   string  $child
     * @return  bool
     */
    public function isAncestorOf($parent, $child)
    {
        return $this->isDescendantOf($child, $parent);
    }

    /**
     * Determines if a type is a descendant of another type.
     *
     * @param   string  $child
     * @param   string  $parent
     * @return  bool
     */
    public function isDescendantOf($child, $parent)
    {
        $childMeta = $this->getMetadataFor($child);
        if (false === $childMeta->isChildEntity()) {
            return false;
        }
        if ($childMeta->getParentEntityType() === $parent) {
            return true;
        }
        return $this->isDescendantOf($childMeta->getParentEntityType(), $parent);
    }

    /**
     * Gets the metadata factory.
     *
     * @return  MetadataFactory
     */
    public function getMetadataFactory()
    {
        return $this->mf;
    }

    /**
     * Gets the entity metadata for a resource type.
     *
     * @param   string  $type
     * @return  \Zarathustra\JsonApiSerializer\Metadata\EntityMetadata
     */
    public function getMetadataFor($type)
    {
        return $this->mf->getMetadataForType($type);
    }
}

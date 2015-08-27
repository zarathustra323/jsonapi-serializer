<?php

namespace Zarathustra\JsonApiSerializer\Metadata;

/**
 * Defines serialization metadata for a relationship field.
 * Should be loaded using the MetadataFactory, not instantiated directly.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class RelationshipMetadata extends FieldMetadata
{
    /**
     * The entity this field is related to.
     *
     * @var EntityMetadata
     */
    public $entity;

    /**
     * The relationship type: one or many
     *
     * @var string
     */
    public $type;

    /**
     * Determines if this is an inverse (non-owning) relationship.
     *
     * @var bool
     */
    public $isInverse = false;

    /**
     * Constructor.
     *
     * @param   string  $type   The relationship type.
     */
    public function __construct($key, $type, EntityMetadata $entity)
    {
        parent::__construct($key);
        $this->setType($type);
        $this->entity = $entity;
    }

    /**
     * Gets the EntityMetadata that this field is related to.
     *
     * @return  EntityMetadata
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Gets the relationship type.
     *
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Determines if this is a one (single) relationship.
     *
     * @return  bool
     */
    public function isOne()
    {
        return 'one' === $this->getType();
    }

    /**
     * Determines if this is a many relationship.
     *
     * @return bool
     */
    public function isMany()
    {
        return 'many' === $this->getType();
    }

    /**
     * Sets the relationship type: one or many.
     *
     * @param   string  $type
     * @return  self
     */
    public function setType($type)
    {
        $this->validateType($type);
        $this->type = strtolower($type);
        return $this;
    }

    /**
     * Validates the relationship type.
     *
     * @param   string  $type
     * @return  bool
     * @throws  \InvalidArgumentException
     */
    protected function validateType($type)
    {
        $type = strtolower($type);
        if (!in_array($type, ['one', 'many'])) {
            throw new \InvalidArgumentException(sprintf('The relationship type "%s" is not valid. Valid types are "one" or "many"', $type));
        }
        return true;
    }
}

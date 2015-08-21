<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure;

class RelationshipMany extends Relationship
{
    /**
     * The relationship data.
     *
     * @var Resource[]
     */
    protected $data = [];

    /**
     * {@inheritDoc}
     */
    public function applyData(Resource $data)
    {
        return $this->data[] = $data;
    }
}

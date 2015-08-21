<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure;

class RelationshipOne extends Relationship
{
    /**
     * {@inheritDoc}
     */
    public function applyData(Resource $data)
    {
        return $this->data = $data;
    }
}

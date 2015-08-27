<?php

namespace Zarathustra\JsonApiSerializer\DocumentStructure;

/**
 * Interface that is shared between a single resource and a collection of multiple resources.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
interface RelatedDataInterface
{
    /**
     * Gets the entity type of the resource or the collection.
     *
     * @return  string
     */
    public function getType();
}

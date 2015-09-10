<?php

namespace Zarathustra\ModlrData;

use Zarathustra\ModlrData\Metadata\MetadataFactory;

/**
 * Responsible for fetching, creating, updating, and deleting entities.
 *
 * @todo    Add support for updating/removing to-one relationships; updating/adding/removing to-many relationships.
 * @author  Jacob Bare <jbare@southcomm.com>
 */
class EntityStore
{
    private $mf;

    public function __construct(MetadataFactory $mf)
    {
        $this->mf = $mf;
    }

    public function findRecord($type, $id, array $fields = [], array $includes = [])
    {
        var_dump($type, $id);
        // If isset in the store, return directlty?

    }

    public function findMany($type, array $ids)
    {

    }

    public function findAll($type)
    {

    }

    public function fetchRelationship($type, $id, $relatedKey)
    {

    }

    public function query($type, array $query)
    {

    }

    public function createRecord($type, array $keyValues)
    {

    }

    public function updateRecord($type, $id, array $keyValues)
    {

    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getSerializer()
    {
        return $this->getAdapter()->getSerializer();
    }

    public function deleteRecord($type, $id)
    {

    }
}

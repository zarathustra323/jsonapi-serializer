<?php

namespace Zarathustra\ModlrData\Persistence;

interface StoreInterface
{
    public function findRecord($type, $id, array $fields = [], array $includes = []);

    public function findMany($type, array $ids);

    public function findAll($type);

    public function fetchRelationship($type, $id, $relatedKey);

    public function query($type, array $query);

    public function createRecord($type, array $keyValues);

    public function updateRecord($type, $id, array $keyValues);
}

<?php

namespace DoctrineExtensions\REST\EntityManager;

interface WrapperInterface
{
    public function createQueryBuilder();
    public function getMetadataFactory();
    public function remove($entity);
    public function flush();
    public function find($entity, $id);
}
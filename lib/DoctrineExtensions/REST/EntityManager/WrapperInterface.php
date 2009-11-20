<?php

namespace DoctrineExtensions\REST\EntityManager;

interface WrapperInterface
{
    public function createQueryBuilder();
    public function createQuery($dql);
    public function getMetadataFactory();
    public function persist($entity);
    public function remove($entity);
    public function flush();
    public function find($entity, $id);
}
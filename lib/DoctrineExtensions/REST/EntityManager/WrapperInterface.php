<?php

namespace DoctrineExtensions\REST\EntityManager;

interface WrapperInterface
{
    function createQueryBuilder();
    function getMetadataFactory();
    function remove($entity);
    function flush();
    function find($entity, $id);
}
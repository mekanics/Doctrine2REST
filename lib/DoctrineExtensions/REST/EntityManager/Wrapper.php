<?php

namespace DoctrineExtensions\REST\EntityManager;

use Doctrine\ORM\EntityManager;

class Wrapper implements WrapperInterface
{
    protected $_em;

    public function __construct(EntityManager $em)
    {
        $this->_em = $em;
    }

    public function createQueryBuilder()
    {
        return $this->_em->createQueryBuilder();
    }

    public function createQuery($dql)
    {
        return $this->_em->createQuery($dql);
    }

    public function getMetadataFactory()
    {
        return $this->_em->getMetadataFactory();
    }

    public function persist($entity)
    {
        return $this->_em->persist($entity);
    }

    public function remove($entity)
    {
        return $this->_em->remove($entity);
    }

    public function flush()
    {
        return $this->_em->flush();
    }

    public function find($entity, $id)
    {
        return $this->_em->find($entity, $id);
    }
}
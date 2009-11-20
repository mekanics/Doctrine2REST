<?php

namespace DoctrineExtensions\REST\Action;

class Insert extends AbstractAction implements ActionInterface
{
    public function getTitle()
    {
        return 'Insert Entity';
    }

    public function getDescription()
    {
        return 'Create a new entity, update the properties and insert it.';
    }

    public function getRequiredParameters()
    {
        return array(
            '_entity'
        );
    }

    public function getOptionalParameters()
    {
        return array();
    }

    public function getRequiredMethod()
    {
        return 'post';
    }

    public function getExampleRequestData()
    {
        return array(
            '_entity' => 'User',
            'username' => 'jwage'
        );
    }

    public function execute()
    {
        $entityName = $this->_request['_entity'];
        $entity = new $entityName();
        $this->_updateEntityInstance($entity);
        $this->_em->persist($entity);
        $this->_em->flush();

        return $entity;
    }
}
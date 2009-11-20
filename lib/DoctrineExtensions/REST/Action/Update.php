<?php

namespace DoctrineExtensions\REST\Action;

class Update extends AbstractAction implements ActionInterface
{
    public function getTitle()
    {
        return 'Update Entity';
    }

    public function getDescription()
    {
        return 'Get existing entity, update the properties and update it.';
    }

    public function getRequiredParameters()
    {
        return array(
            '_entity',
            '_id'
        );
    }

    public function getRequiredMethod()
    {
        return 'put';
    }

    public function getExampleRequestData()
    {
        return array(
            '_entity' => 'User',
            '_id' => '1',
            'username' => 'jonwage'
        );
    }

    public function execute()
    {
        if ($entity = $this->_em->find($this->_request['_entity'], $this->_request['_id'])) {
            $this->_updateEntityInstance($entity);
            $this->_em->flush();
        }

        return $entity;
    }
}
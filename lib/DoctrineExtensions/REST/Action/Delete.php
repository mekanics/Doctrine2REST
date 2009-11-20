<?php

namespace DoctrineExtensions\REST\Action;

class Delete extends AbstractAction implements ActionInterface
{
    public function getTitle()
    {
        return 'Delete Entity';
    }

    public function getDescription()
    {
        return 'Delete entities by their identifiers.';
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
        return 'delete';
    }

    public function getExampleRequestData()
    {
        return array(
            '_entity' => 'User',
            '_id' => '1'
        );
    }

    public function execute()
    {
        $query = $this->_getFindByIdQuery($this->_request['_entity'], $this->_request['_id']);
        $entities = $query->execute();

        if ($entities) {
            foreach ($entities as $entity) {
                $this->_em->remove($entity);
            }
            $this->_em->flush();
        }
        return $entities;
    }
}
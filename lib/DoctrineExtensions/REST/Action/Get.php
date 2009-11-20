<?php

namespace DoctrineExtensions\REST\Action;

class Get extends AbstractAction implements ActionInterface
{
    public function getTitle()
    {
        return 'Get Entity';
    }

    public function getDescription()
    {
        return 'Get entities by their identifiers.';
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
        return 'get';
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
        $this->_setQueryFirstAndMax($query);

        $result = $query->execute();

        if ( ! $result) {
            throw new \InvalidArgumentException(sprintf('Could not find the "%s" with an ids of "%s"', $this->_request['_entity'], implode(', ', (array) $this->_request['_id'])));
        }
        return $result;
    }
}
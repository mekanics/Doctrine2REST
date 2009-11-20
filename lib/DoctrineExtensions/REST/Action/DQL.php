<?php

namespace DoctrineExtensions\REST\Action;

use Doctrine\ORM\EntityManager,
    DoctrineExtensions\REST\Request,
    DoctrineExtensions\REST\RequestHandler;

class DQL extends AbstractAction implements ActionInterface
{
    private $_type;
    private $_requiredMethod = 'get';

    public function __construct(RequestHandler $requestHandler)
    {
        parent::__construct($requestHandler);

        $e = explode(' ', $this->_request['_query']);
        $this->_type = strtoupper($e[0]);

        if ($this->_type == 'UPDATE') {
            $this->_requiredMethod = 'put';
        } else if ($this->_type == 'DELETE') {
            $this->_requiredMethod = 'delete';
        } else {
            $this->_requiredMethod = 'get';
        }
    }

    public function getTitle()
    {
        return 'Execute DQL Query';
    }

    public function getDescription()
    {
        return 'Execute a DQL Query and return the results.';
    }

    public function getRequiredParameters()
    {
        return array(
            '_query'
        );
    }

    public function getRequiredMethod()
    {
        return $this->_requiredMethod;
    }

    public function getExampleRequestData()
    {
        return array(
            '_query' => 'SELECT u FROM User u WHERE u.id = 1'
        );
    }

    public function execute()
    {
        $query = $this->_em->createQuery($this->_request['_query']);
        $this->_setQueryFirstAndMax($query);

        try {
            $result = $query->execute($this->_gatherData($this->_request->getData()));
        } catch (\Exception $e) {
            throw new \Exception(sprintf('DQL query "%s" failed with exception "%s."', $this->_request['_query'], $e->getMessage()));
        }

        if ($this->_type !== 'SELECT' && ! $result) {
            throw new \Exception(sprintf('The query did not return any affected rows.', $this->_type));
        }

        return $result;
    }
}
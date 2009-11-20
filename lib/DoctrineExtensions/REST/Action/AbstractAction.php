<?php

namespace DoctrineExtensions\REST\Action;

use \DoctrineExtensions\REST\RequestHandler;

abstract class AbstractAction
{
    protected $_requestHandler;
    protected $_em;
    protected $_request;

    public function __construct(RequestHandler $requestHandler)
    {
        $this->_requestHandler = $requestHandler;
        $this->_em = $requestHandler->getEntityManager();
        $this->_request = $requestHandler->getRequest();
    }

    abstract public function getRequiredParameters();
    abstract public function getRequiredMethod();
    abstract public function getExampleRequestData();
    abstract public function getTitle();
    abstract public function getDescription();

    protected function _setQueryFirstAndMax($q)
    {
        if ( ! isset($this->_request['_page']) && ! isset($this->_request['_first']) && ! isset($this->_request['_max'])) {
            $this->_request['_page'] = '1';
        }
        $maxPerPage = isset($this->_request['_max_per_page']) ? $this->_request['_max_per_page'] : 20;
        if (isset($this->_request['_page'])) {
            $page = $this->_request['_page'];
            $first = ($page - 1) * $maxPerPage;
            $q->setFirstResult($first);
            $q->setMaxResults($maxPerPage);
        } else {
            if (isset($this->_request['_first'])) {
                $q->setFirstResult($this->_request['_first']);
            } else {
                $q->setFirstResult(0);
            }
            if (isset($this->_request['_max'])) {
                $q->setMaxResults($this->_request['_max']);
            } else {
                $q->setMaxResults($maxPerPage);
            }
        }
    }

    protected function _updateEntityInstance($entity)
    {
        $data = $this->_gatherData($this->_request->getData());
        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (is_callable(array($entity, $setter))) {
                $entity->$setter($value);
            } else {
                throw new \BadMethodCallException(sprintf('Invalid "%s" property named "%s"', get_class($entity), $key));
            }
        }
        return $entity;
    }

    protected function _getFindByIdQuery($entity, $id)
    {
        $id = (array) $id;

        $qb = $this->_em->createQueryBuilder()
            ->select('a')
            ->from($entity, 'a');
        
        foreach ($id as $key => $value) {
            $qb->orWhere("a.id = '$value'");
        }
        $q = $qb->getQuery();
        return $q;
    }

    protected function _gatherData()
    {
        $data = array();
        foreach ($this->_request->getData() as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }
            $data[$key] = $value;
        }
        return $data;
    }

    protected function _validateRequiredParameters()
    {
        foreach ($this->getRequiredParameters() as $name) {
            if ( ! isset($this->_request[$name])) {
                throw new \InvalidArgumentException(sprintf('The "%s" action requires a parameter named "%s."', $this->_request['_action'], $name));
            }
        }
    }

    protected function _validateRequiredMethod()
    {
        if ($this->getRequiredMethod() !== $this->_request['_method']) {
            throw new \InvalidArgumentException(sprintf('This action named "%s" requires a request method of "%s."', $this->_request['_action'], $this->getRequiredMethod()));
        }
    }

    protected function _validateFormat()
    {
        $allowedFormats = array(
            'xml',
            'json',
            'php'
        );
        if ( ! in_array($this->_request['_format'], $allowedFormats)) {
            throw new \InvalidArgumentException(sprintf('Invalid format specified "%s"', $this->_request['_format']));
        }
    }

    public function validate()
    {
        $this->_validateRequiredParameters();
        $this->_validateRequiredMethod();
        $this->_validateFormat();
    }
}
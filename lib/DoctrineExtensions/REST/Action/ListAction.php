<?php

namespace DoctrineExtensions\REST\Action;

class ListAction extends AbstractAction implements ActionInterface
{
    public function getTitle()
    {
        return 'List Entities';
    }

    public function getDescription()
    {
        return 'List entities of a certain type.';
    }

    public function getRequiredParameters()
    {
        return array(
            '_entity'
        );
    }

    public function getRequiredMethod()
    {
        return 'get';
    }

    public function getExampleRequestData()
    {
        return array(
            '_entity' => 'User'
        );
    }

    public function execute()
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('a')
            ->from($this->_request['_entity'], 'a');

        $data = $this->_gatherData();
        foreach ($data as $key => $value) {
            $qb->andWhere(sprintf("a.%s = '$value'", $key));
        }

        $query = $qb->getQuery();
        $this->_setQueryFirstAndMax($query);
        return $query->execute();
    }
}
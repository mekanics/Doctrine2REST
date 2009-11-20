<?php

namespace Doctrine2REST\Tests;

require_once __DIR__ . '/TestInit.php';

class ServerTest extends \PHPUnit_Framework_TestCase
{
    private $_em;

    public function setUp()
    {
        $this->_em = new EntityManagerTest();
    }

    public function testRequestHandlerGetAction()
    {
        $requestHandler = $this->_getTestRequestHandler(array());
        $requestHandler->registerAction('test', '\Doctrine2REST\Tests\TestAction');

        $this->assertTrue($requestHandler->getAction('delete') instanceof \DoctrineExtensions\REST\Action\Delete);
        $this->assertTrue($requestHandler->getAction('test') instanceof \Doctrine2REST\Tests\TestAction);
    }

    public function testRequestHandlerGetResponse()
    {
        $requestData = array(
            '_action' => 'list',
            '_method' => 'get',
            '_entity' => 'Doctrine2REST\Tests\TestEntity',
            'username' => 'jwage'
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $this->assertTrue($requestHandler->getResponse() instanceof \DoctrineExtensions\REST\Response);
    }

    public function testList()
    {
        $requestData = array(
            '_action' => 'list',
            '_method' => 'get',
            '_entity' => 'Doctrine2REST\Tests\TestEntity',
            'username' => 'jwage'
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $qb = $this->_em->getLastQueryBuilder();

        $this->assertTrue(isset($qb->select[0][0]));
        $this->assertTrue(isset($qb->from[0][0]));
        $this->assertEquals('Doctrine2REST\Tests\TestEntity', $qb->from[0][0]);
        $this->assertTrue(isset($qb->from[0][1]));
        $this->assertTrue(isset($qb->andWhere[0][0]));
        $this->assertEquals("a.username = 'jwage'", $qb->andWhere[0][0]);
    }

    public function testGet()
    {
        $requestData = array(
            '_action' => 'get',
            '_method' => 'get',
            '_entity' => 'Doctrine2REST\Tests\TestEntity',
            '_id' => '1'
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $qb = $this->_em->getLastQueryBuilder();

        $this->assertTrue(isset($qb->select[0][0]));
        $this->assertTrue(isset($qb->from[0][0]));
        $this->assertEquals('Doctrine2REST\Tests\TestEntity', $qb->from[0][0]);
        $this->assertTrue(isset($qb->from[0][1]));
        $this->assertTrue(isset($qb->orWhere[0][0]));
        $this->assertEquals("a.id = '1'", $qb->orWhere[0][0]);
    }

    public function testMultipleGet()
    {
        $requestData = array(
            '_action' => 'get',
            '_method' => 'get',
            '_entity' => 'Doctrine2REST\Tests\TestEntity',
            '_id' => array('1', '2')
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $qb = $this->_em->getLastQueryBuilder();

        $this->assertTrue(isset($qb->select[0][0]));
        $this->assertTrue(isset($qb->from[0][0]));
        $this->assertEquals('Doctrine2REST\Tests\TestEntity', $qb->from[0][0]);
        $this->assertTrue(isset($qb->from[0][1]));
        $this->assertTrue(isset($qb->orWhere[0][0]));
        $this->assertEquals("a.id = '1'", $qb->orWhere[0][0]);
        $this->assertEquals("a.id = '2'", $qb->orWhere[1][0]);
    }

    public function testDelete()
    {
        $requestData = array(
            '_action' => 'delete',
            '_method' => 'delete',
            '_entity' => 'Doctrine2REST\Tests\TestEntity',
            '_id' => array('1', '2')
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $this->assertEquals(array('id0' => 1, 'id1' => 2), (array) $data->request->id);
        $this->assertEquals('test', $data->results[0]);
        $this->assertEquals(1, $data->success);
        $this->assertEquals(array('test'), $this->_em->removed);
    }

    public function testUpdate()
    {
        $requestData = array(
            '_action' => 'update',
            '_method' => 'put',
            '_entity' => 'Doctrine2REST\Tests\TestEntity',
            '_id' => 1,
            'username' => 'okok'
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $this->assertTrue($this->_em->flushed);
        $this->assertEquals('okok', $data->results->username);
        $this->assertEquals('Doctrine2REST\Tests\TestEntity', $data->results->class_name);
    }

    public function testInsert()
    {
        $requestData = array(
            '_action' => 'insert',
            '_method' => 'post',
            '_entity' => 'Doctrine2REST\Tests\TestEntity',
            'username' => 'new data'
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $this->assertTrue($this->_em->flushed);
        $this->assertEquals('new data', $data->results->username);
        $this->assertEquals('Doctrine2REST\Tests\TestEntity', $data->results->class_name);
    }

    public function testDql()
    {
        $requestData = array(
            '_action' => 'dql',
            '_method' => 'get',
            '_query' => 'SELECT u FROM Doctrine2REST\Tests\TestEntity u'
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $this->assertEquals('test', $data->results[0]);
        $this->assertEquals(array('SELECT u FROM Doctrine2REST\Tests\TestEntity u'), $this->_em->dql);
    }

    public function testDqlDeleteMethod()
    {
        $requestData = array(
            '_action' => 'dql',
            '_method' => 'get',
            '_query' => 'DELETE FROM Doctrine2REST\Tests\TestEntity u'
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $this->assertEquals('This action named "dql" requires a request method of "delete."', $data->results->error);
    }

    public function testDqlUpdateMethod()
    {
        $requestData = array(
            '_action' => 'dql',
            '_method' => 'get',
            '_query' => "UPDATE Doctrine2REST\Tests\TestEntity u SET u.username = 'jwage' WHERE u.id = 1"
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $this->assertEquals('This action named "dql" requires a request method of "put."', $data->results->error);
    }

    public function testActions()
    {
        $requestData = array(
            '_action' => 'actions',
            '_method' => 'get'
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $actions = (array) $data->results->actions;
        $this->assertEquals(7, count($actions));

        $this->assertEquals('delete', $actions['action0']->name);
        $this->assertEquals('delete', $actions['action0']->method);

        $this->assertEquals('get', $actions['action1']->name);
        $this->assertEquals('get', $actions['action1']->method);

        $this->assertEquals('insert', $actions['action2']->name);
        $this->assertEquals('post', $actions['action2']->method);

        $this->assertEquals('update', $actions['action3']->name);
        $this->assertEquals('put', $actions['action3']->method);

        $this->assertEquals('list', $actions['action4']->name);
        $this->assertEquals('get', $actions['action4']->method);

        $this->assertEquals('dql', $actions['action5']->name);
        $this->assertEquals('get', $actions['action5']->method);

        $this->assertEquals('actions', $actions['action6']->name);
        $this->assertEquals('get', $actions['action6']->method);
    }

    public function testInvalidMethod()
    {
        $requestData = array(
            '_action' => 'list',
            '_method' => 'post',
            '_entity' => 'Doctrine2REST\Tests\TestEntity',
            'username' => 'jwage'
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $this->assertEquals('This action named "list" requires a request method of "get."', $data->results->error);
    }

    public function testMissingRequiredParameter()
    {
        $requestData = array(
            '_action' => 'list',
            '_method' => 'post'
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $this->assertEquals('The "list" action requires a parameter named "_entity."', $data->results->error);
    }

    public function testInvalidAction()
    {
        $requestData = array(
            '_action' => 'sucks',
            '_method' => 'get'
        );

        $requestHandler = $this->_getTestRequestHandler($requestData);
        $data = json_decode($requestHandler->getResponse()->getOutput());
        $this->assertEquals('The request action named "sucks" is not valid.', $data->results->error);
    }

    private function _getTestRequestHandler(array $requestData)
    {
        $request = new RequestTest($requestData);
        return new RequestHandlerTest($this->_em, $request, new ResponseTest($request));
    }
}

class RequestTest extends \DoctrineExtensions\REST\Request
{
}

class ResponseTest extends \DoctrineExtensions\REST\Response
{
}

class RequestHandlerTest extends \DoctrineExtensions\REST\RequestHandler
{
}

class QueryBuilderTest
{
    public $select = array();
    public $from = array();
    public $andWhere = array();
    public $orWhere = array();

    public function select()
    {
        $this->select[] = func_get_args();
        return $this;
    }

    public function from()
    {
        $this->from[] = func_get_args();
        return $this;
    }

    public function andWhere()
    {
        $this->andWhere[] = func_get_args();
        return $this;
    }

    public function orWhere()
    {
        $this->orWhere[] = func_get_args();
        return $this;
    }

    public function getQuery()
    {
        return new QueryTest('SELECT u FROM Doctrine2REST\Tests\TestEntity u');
    }
}

class QueryTest
{
    public $dql;

    public function __construct($dql)
    {
        $this->dql = $dql;
    }

    public function setFirstResult()
    {
    }

    public function setMaxResults()
    {
    }

    public function execute()
    {
        return array('test');
    }
}

class EntityManagerTest implements \DoctrineExtensions\REST\EntityManager\WrapperInterface
{
    public $qbs = array();
    public $removed = array();
    public $persisted = array();
    public $find = array();
    public $flushed = false;
    public $dql = array();

    public function createQueryBuilder()
    {
        $qb = new QueryBuilderTest();
        $this->qbs[] = $qb;
        return $qb;
    }

    public function createQuery($dql)
    {
        $this->dql[] = $dql;
        return new QueryTest($dql);
    }

    public function getLastQueryBuilder()
    {
        return end($this->qbs);
    }

    public function getMetadataFactory()
    {
        return new MetadataFactoryTest();
    }

    public function remove($entity)
    {
        $this->removed[] = $entity;
    }

    public function persist($entity)
    {
        $this->persisted[] = $entity;
    }

    public function flush()
    {
        $this->flushed = true;
    }

    public function find($entity, $id)
    {
        $this->find[$entity][] = $id;
        $obj = new \Doctrine2REST\Tests\TestEntity();
        $obj->username = 'jwage';
        return $obj;
    }
}

class MetadataFactoryTest
{
    public function getMetadataFor()
    {
        
    }
}

class TestEntity
{
    public $username;

    public function setUsername($username)
    {
        $this->username = $username;
    }
}

class TestAction extends \DoctrineExtensions\REST\Action\AbstractAction implements \DoctrineExtensions\REST\Action\ActionInterface
{
    public function getTitle()
    {
        return 'Test Title';
    }

    public function getDescription()
    {
        return 'Test description.';
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
            '_id' => '1'
        );
    }

    public function execute()
    {
        return array('executed');
    }
}
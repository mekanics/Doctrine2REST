<?php

namespace DoctrineExtensions\REST;

use DoctrineExtensions\REST\EntityManager\WrapperInterface;

class Server
{
    private $_requestHandler;

    public function __construct(WrapperInterface $em, array $requestData = array())
    {
        if ( ! isset($requestData['_method'])) {
            $requestData['_method'] = strtolower($_SERVER['REQUEST_METHOD']);
        }
        $request = new Request($requestData);
        $response = new Response($request);
        $this->_requestHandler = new RequestHandler($em, $request, $response);
    }

    public function registerAction($action, $className)
    {
        $this->_requestHandler->registerAction($action, $className);
    }

    public function setUsername($username)
    {
        $this->_requestHandler->setUsername($username);
    }

    public function setPassword($password)
    {
        $this->_requestHandler->setPassword($password);
    }

    public function run()
    {
        $this->_requestHandler->getResponse()->send();
    }
}
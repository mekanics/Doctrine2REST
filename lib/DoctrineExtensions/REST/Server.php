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
        $this->_requestHandler = new RequestHandler($em, $request, new Response($request));
    }

    public function registerAction($action, $className)
    {
        $this->_requestHandler->registerAction($action, $className);
    }

    public function run()
    {
        $this->_requestHandler->getResponse()->send();
    }
}
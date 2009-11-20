<?php

namespace DoctrineExtensions\REST;

use DoctrineExtensions\REST\EntityManager\WrapperInterface;

class RequestHandler
{
    private $_em;
    private $_request;
    private $_response;
    private $_username;
    private $_password;
    private $_credentialsCallback;

    private $_actions = array(
        'delete' => 'DoctrineExtensions\\REST\\Action\\Delete',
        'get' => 'DoctrineExtensions\\REST\\Action\\Get',
        'insert' => 'DoctrineExtensions\\REST\\Action\\Insert',
        'update' => 'DoctrineExtensions\\REST\\Action\\Update',
        'list' => 'DoctrineExtensions\\REST\\Action\\ListAction',
        'dql' => 'DoctrineExtensions\\REST\\Action\\DQL',
        'actions' => 'DoctrineExtensions\\REST\\Action\Actions'
    );

    public function __construct(WrapperInterface $em, Request $request, Response $response)
    {
        $this->_em = $em;
        $this->_request = $request;
        $this->_response = $response;
        $this->_response->setRequestHandler($this);
        $this->_credentialsCallback = array($this, 'checkCredentials');
    }

    public function setCredentialsCallback($callback)
    {
        $this->_credentialsCallback = $callback;
    }

    public function registerAction($action, $className)
    {
        $this->_actions[$action] = $className;
    }

    public function isSecure()
    {
        return ($this->_username && $this->_password) ? true : false;
    }

    public function checkCredentials($username, $password)
    {
        if ( ! $this->isSecure()) {
            return true;
        }

        if ($this->_username == $username && $this->_password == $password) {
            return true;
        } else {
            return false;
        }
    }

    public function hasValidCredentials()
    {
        $args = array($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        return call_user_func_array($this->_credentialsCallback, $args);
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function setUsername($username)
    {
        $this->_username = $username;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function setPassword($password)
    {
        $this->_password = $password;
    }

    public function getActions()
    {
        return $this->_actions;
    }

    public function getEntityManager()
    {
        return $this->_em;
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function getResponse()
    {
        try {
            $this->_executeAction();
        } catch (\Exception $e) {
            $this->_response->setError($this->_getExceptionErrorMessage($e));
        }
        return $this->_response;
    }

    public function getAction($actionName)
    {
        if ( ! is_object($this->_actions[$actionName])) {
            $actionClassName = $this->_actions[$actionName];
            if (class_exists($actionClassName)) {
                $this->_actions[$actionName] = new $actionClassName($this);
            } else {
                throw new \InvalidMethodException(sprintf('Invalid action specified %s', $action));
            }
        }
        return $this->_actions[$actionName];
    }

    private function _validateRequest()
    {
        if ( ! isset($this->_request['_action'])) {
            $this->_request['_action'] = 'actions';
        }

        if ( ! isset($this->_actions[$this->_request['_action']])) {
            throw new \InvalidArgumentException(sprintf('The request action named "%s" is not valid.', $this->_request['_action']));
        }
    }

    private function _executeAction()
    {
        $this->_validateRequest();

        $actionInstance = $this->getAction($this->_request['_action']);
        $actionInstance->validate();

        $result = $actionInstance->execute();

        if ($result !== false) {
            $this->_response->setResponseData($this->_transformResultForResponse($result));
        } else {
            $this->_response->setError(sprintf('An error occurred executing the action named "%s" with a request method of "%s."', $this->_request['_action'], $this->_request['_method']));
        }
    }

    private function _getExceptionErrorMessage(\Exception $e)
    {
        $message = $e->getMessage();

        if ($e instanceof \PDOException) {
            $message = preg_replace("/SQLSTATE\[.*\]: (.*)/", "$1", $message);
        }

        return $message;
    }

    private function _transformResultForResponse($result, $array = null)
    {
        if ( ! $array) {
            $array = array();
        }
        if (is_object($result)) {
            $entityName = get_class($result);
            $array['class_name'] = $entityName;
            try {
                $class = $this->_em->getMetadataFactory()->getMetadataFor($entityName);
                foreach ($class->fieldMappings as $fieldMapping) {
                    $array[$fieldMapping['fieldName']] = $class->getReflectionProperty($fieldMapping['fieldName'])->getValue($result);
                }
            } catch (\Exception $e) {
                $vars = get_object_vars($result);
                foreach ($vars as $key => $value) {
                    $array[$key] = $value;
                }
            }
        } else if (is_array($result)) {
            foreach ($result as $key => $value) {
                if (is_object($value) || is_array($value)) {
                    if (is_object($value)) {
                        $key = 'result' . $key;
                        $array[$key]['class_name'] = get_class($value);
                    }
                    $array[$key] = $this->_transformResultForResponse($value, isset($array[$key]) ? $array[$key] : array());
                } else {
                    $array[$key] = $value;
                }
            }
        } else if (is_string($result)) {
            $array = $result;
        }
        return $array;
    }
}
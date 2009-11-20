<?php

namespace DoctrineExtensions\REST\Action;

class Actions extends AbstractAction implements ActionInterface
{
    public function getTitle()
    {
        return 'Available Actions';
    }

    public function getDescription()
    {
        return 'View a list of all the available REST actions.';
    }

    public function getRequiredParameters()
    {
        return array();
    }

    public function getRequiredMethod()
    {
        return 'get';
    }

    public function getExampleRequestData()
    {
        return array();
    }

    public function execute()
    {
        $request = $this->_requestHandler->getRequest();
        $actions = array();
        $actions['actions'] = array();
        $count = 0;
        foreach ($this->_requestHandler->getActions() as $name => $class) {
            $actionInstance = $this->_requestHandler->getAction($name);
            $requiredParameters = $actionInstance->getRequiredParameters();

            $action = array();
            $action['title'] = $actionInstance->getTitle();
            $action['description'] = $actionInstance->getDescription();
            $action['name'] = $name;
            $action['method'] = $actionInstance->getRequiredMethod();
            $action['required'] = implode(', ', $requiredParameters);

            if (isset($_SERVER['HTTP_HOST'])) {
                $action['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
                $action['url'] .= '?';

                $parameters = array();
                foreach ($requiredParameters as $key => $value) {
                    $parameters[] = $value . '=:' . $value;
                }
                $parameters[] = '_format=' . $request['_format'];
                $parameters[] = '_action=' . $name;
                $action['url'] .= implode('&', $parameters);

                $parameters = array();
                $parameters['_action'] = $name;
                $parameters['_format'] = $request['_format'];

                $exampleRequestData = $actionInstance->getExampleRequestData();
                if ($action['method'] == 'get') {
                    $action['example'] = 'curl ';
                    $parameters = array_merge($parameters, $exampleRequestData);
                } else if ($action['method'] == 'post') {
                    $action['example'] = sprintf('curl -d "%s"', http_build_query($exampleRequestData));
                } else if ($action['method'] == 'delete') {
                    $action['example'] = sprintf('curl -d "%s"', http_build_query($exampleRequestData));
                    $parameters['_method'] = 'delete';
                } else if ($action['method'] == 'put') {
                    $action['example'] = 'curl -T ';
                }

                $action['example'] .= ' http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

                if ($parameters) {
                    $action['example'] .= '?' . http_build_query($parameters);
                }
            }

            $actions['actions']['action'.$count] = $action;
            $count++;
        }
        return $actions;
    }
}
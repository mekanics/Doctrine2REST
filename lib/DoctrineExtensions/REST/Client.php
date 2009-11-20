<?php

namespace DoctrineExtensions\REST;

class Client
{
    private $_url;
    private $_actions = array();
    private $_username;
    private $_password;

    public function __construct($url, $username = null, $password = null)
    {
        $this->_username = $username;
        $this->_password = $password;
        $this->_url = $url;
        $data = $this->_getUrl($this->_url);
        foreach ($data->results->actions as $act) {
            $action = array();
            $action['method'] = $act->method;
            $action['url'] = $act->url;
            $action['required'] = array();
            if ($act->required) {
                $action['required'] = explode(', ', $act->required);
            }
            $this->_actions[$act->name] = $action;
        }
    }

    private function _getUrl($url, $method = 'get')
    {
        if ($this->_username && $this->_password) {
            $context = stream_context_create(array(
                'http' => array(
                    'method'  => $method,
                    'header'  => sprintf("Authorization: Basic %s\r\n", base64_encode($this->_username . ':' . $this->_password)).
                        "Content-type: application/x-www-form-urlencoded\r\n",
                    'timeout' => 5,
                )
            ));
        } else {
            $context = stream_context_create(array(
                'http' => array(
                    'method'  => $method,
                    'timeout' => 5,
                )
            ));
        }
        $data = file_get_contents($url, false, $context);
        $data = json_decode($data);
        if (isset($data->results->error)) {
            throw new \Exception($data->results->error);
        }
        return $data;
    }

    public function executeAction($action, array $parameters = array())
    {
        if (  ! isset($this->_actions[$action])) {
            throw new \InvalidArgumentException(sprintf('Invalid action named "%s"', $action));
        }
        $action = $this->_actions[$action];
        if (isset($parameters[0])) {
            $params = $parameters;
            $parameters = array();
            foreach ($params as $key => $value) {
                if (is_numeric($key)) {
                    $parameters[$action['required'][$key]] = $value;
                } else {
                    $parameters[$key] = $value;
                }
            }
        }
        $parameters['_method'] = $action['method'];

        $required = array();
        foreach ($action['required'] as $name) {
            if ( ! isset($parameters[$name])) {
                throw new \InvalidArgumentException(sprintf('Missing required parameter named "%s"', $name));
            }
            $required[$name] = $parameters[$name];
        }
        $url = $action['url'];
        foreach ($required as $key => $value) {
            if ( ! is_array($value)) {
                $url = str_replace(sprintf(':%s', $key), $value, $url);
            }
        }

        $url = str_replace('&_id=:_id', null, $url);
        $j = strstr($url, '?') ? '&' : '?';
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $value;
        }
        $url .= $j . http_build_query($parameters);
        $url = str_replace(' ', '%20', $url);

        return $this->_getUrl($url, $action['method']);
    }

    public function __call($method, $arguments)
    {
        $callback = function($matches) {
            return '_' . strtolower($matches[0]);
        };
        $action = preg_replace_callback('/[A-Z+]/', $callback, $method);

        if (isset($arguments[0]) && is_array($arguments[0])) {
            $arguments = $arguments[0];
        }
        $end = end($arguments);
        if (is_array($end) && ! isset($end[0])) {
            array_pop($arguments);
            $arguments = array_merge($arguments, $end);
        }
        return $this->executeAction($action, $arguments);
    }
}
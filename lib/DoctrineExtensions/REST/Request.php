<?php

namespace DoctrineExtensions\REST;

class Request implements \ArrayAccess
{
    private $_data;

    public function __construct(array $request)
    {
        $this->_data = $request;
        $this->_data['_format'] = isset($this->_data['_format']) ? $this->_data['_format'] : 'json';
    }

    public function getData()
    {
        return $this->_data;
    }

    public function offsetSet($key, $value)
    {
        $this->_data[$key] = $value;
    }

    public function offsetGet($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    public function offsetUnset($key)
    {
        unset($this->_data[$key]);
    }

    public function offsetExists($key)
    {
        return isset($this->_data[$key]);
    }
}
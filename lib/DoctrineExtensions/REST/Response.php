<?php

namespace DoctrineExtensions\REST;

class Response
{
    private $_request;
    private $_responseData;

    public function __construct(Request $request)
    {
        $this->_request = $request;
    }

    public function setError($error)
    {
        $this->_responseData = array();
        $this->_responseData['error'] = $error;
    }

    public function setResponseData($responseData)
    {
        $this->_responseData = $responseData;
    }

    public function send()
    {
        $this->_sendHeaders();
        echo $this->getOutput();
    }

    private function _sendHeaders()
    {
        switch ($this->_request['_format']) {
            case 'php':
                header('Content-type: text/html;');
            break;

            case 'json':
                header('Content-type: text/json;');
                header('Content-Disposition: attachment; filename="' . $this->_request['_action'] . '.json"');
            break;

            case 'xml':
            default:
                header('Content-type: application/xml;');
        }
    }

    public function getOutput()
    {
        $data = array();
        $data['request'] = array();
        foreach ($this->_request->getData() as $key => $value) {
            if ($key[0] == '_') {
                $key = substr($key, 1);
            }
            if (is_array($value)) {
                $val = $value;
                $value = array();
                foreach ($val as $k => $v) {
                    $value[$key . $k] = (string) $v;
                }
            }

            $data['request'][$key] = $value;
        }
        if ( ! isset($this->_responseData['error'])) {
            $data['success'] = 1;
        }
        $data['results'] = $this->_responseData;

        switch ($this->_request['_format']) {
            case 'php':
                return serialize($data);
            break;

            case 'json':
                return json_encode($data);
            break;

            case 'xml':
            default:
                return $this->_arrayToXml($data);
        }
    }

    private function _arrayToXml($array, $rootNodeName = 'doctrine', $xml = null, $charset = null)
    {
        if ($xml === null) {
            $xml = new \SimpleXmlElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><$rootNodeName/>");
        }

        foreach($array as $key => $value) {
            $key = preg_replace('/[^A-Za-z_]/i', '', $key);

            if (is_array($value) && ! empty($value)) {
                $node = $xml->addChild($key);

                foreach ($value as $k => $v) {
                    if (is_integer($v)) {
                        unset($value[$k]);
                        $node->addAttribute($k, $v);
                    }
                }

                $this->_arrayToXml($value, $rootNodeName, $node, $charset);
            } else if (is_int($key)) {               
                $xml->addChild($value, 'true');
            } else if ($value) {
                $charset = $charset ? $charset : 'utf-8';
                if (strcasecmp($charset, 'utf-8') !== 0 && strcasecmp($charset, 'utf8') !== 0) {
                    $value = iconv($charset, 'UTF-8', $value);
                }
                $value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                $xml->addChild($key, $value);
            }
        }

        return $this->_formatXml($xml);
    }

    private function _formatXml($simpleXml)
    {
        $xml = $simpleXml->asXml();

        // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

        // now indent the tags
        $token = strtok($xml, "\n");
        $result = ''; // holds formatted version as it is built
        $pad = 0; // initial indent
        $matches = array(); // returns from preg_matches()

        // test for the various tag states
        while ($token !== false) {
            // 1. open and closing tags on same line - no change
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) {
                $indent = 0;
            // 2. closing tag - outdent now
            } else if (preg_match('/^<\/\w/', $token, $matches)) {
                $pad = $pad - 4;
            // 3. opening tag - don't pad this one, only subsequent tags
            } elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) {
                $indent = 4;
            // 4. no indentation needed
            } else {
                $indent = 0; 
            }

            // pad the line with the required number of leading spaces
            $line = str_pad($token, strlen($token)+$pad, ' ', STR_PAD_LEFT);
            $result .= $line . "\n"; // add to the cumulative result, with linefeed
            $token = strtok("\n"); // get the next token
            $pad += $indent; // update the pad size for subsequent lines    
        }
        return $result;
    }
}
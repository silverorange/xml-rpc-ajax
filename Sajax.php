<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// {{{ request type constants

/**
 * Constant for a HTTP GET request
 */
define('HTTP_SAJAX_TYPE_GET', 'GET');

/**
 * Constant for a HTTP POST request
 */
define('HTTP_SAJAX_TYPE_POST', 'POST');

// }}}

/**
 * Simple AJAX library for PHP
 *
 * AJAX stands for Asynchronous Javascript and XML and is a meme coined by
 * the company Adaptive Path. The techniques used by AJAX work in all modern
 * browsers and involve a javascript HTTP request object and the ability to
 * manipulate the document object through javascript.
 *
 * This package allows you to use AJAX techniques to call server side PHP
 * functions from javascript without causing a full page request.
 *
 * Function calls occur as follows:
 *
 * 1. Server renders javascript on client browser.
 * 2. Client javascript calls function wrapper written in javascript.
 * 3. Function wrapper uses XMLHttpRequest object to ask the server to
 *    execute the function in PHP.
 * 4. HTTP request is sent to the server and server responds by rendering
 *    an XML document with PHP. The XML document contains the return value of
 *    the functon on the server.
 * 5. Client javascript finishes receiving server response and parses XML to
 *    retrieve return value of server side function.
 * 
 * Derived from the Sajax PHP library written by ModernMethod Inc and licensed
 * under a BSD style license. See "http://modernmethod.com/sajax/".
 *
 * PHP Version 5
 *
 * @category  HTTP
 * @package   HTTP_Sajax
 * @author    Thomas Lackner
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2005 ModernMethod Inc. and silverorange Inc.
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link      http://pear.php.net/package/HTTP_Sajax
 */
class HTTP_Sajax
{
    // {{{ public properties

    /**
     * Whether debugging mode is on or off
     *
     * @var boolean
     */
    public $debug_mode = false;

    /**
     * The uri of the server side request handler
     *
     * This uri is the uri used for all asynchronous javascript calls. It
     * defaults to the current page request uri.
     *
     * @var string
     */
    public $remote_uri = '';

    /**
     * An identifier string for this Sajax object
     *
     * The identifier string is used in javascript to reference a particular
     * object instance. A javascript object instance is created with this
     * identifier when the displayJavascript() method is called and javascript
     * function wrappers for PHP methods are attached to the javascript object
     * instance.
     *
     * @var string
     *
     * @see HTTP_Sajax::displayJavascript()
     */
    public $id = '';

    // }}}
    // {{{ private properties

    /**
     * An array of PHP function names to be made available to javascript
     *
     * @var array
     */
    private $_export_list = array();

    /**
     * The type of HTTP request to use for server calls
     *
     * @var string
     */
    private $_request_type = '';

    // }}}
    // {{{ public function __construct()
    
    /**
     * Creates a new Simple AJAX object
     *
     * @param string $id an identifier for this Sajax object that is used in
     *                    client-side javascript.
     *
     * @param string $request_type the type of HTTP request to use for server
     *                              calls. If an invalid type is specified, the
     *                              default of {@link HTTP_SAJAX_TYPE_GET} is
     *                              used.
     */
    public function __construct($id, $request_type = HTTP_SAJAX_TYPE_GET)
    {
        $this->id = $id;

        if (!$this->setRequestType($request_type)) {
            $this->_request_type = HTTP_SAJAX_TYPE_GET;
        }

        $this->remote_uri = $_SERVER['REQUEST_URI'];
    }

    // }}}
    // {{{ public function exportPHPFunction()

    /**
     * Exports a PHP function making it accessable to javascript
     *
     * The exported server side PHP function is made available to client
     * side javascript through a dynamically generated function stub.
     *
     * To call an exported function in client-side javascript, use the
     * javascript function stub that is automatically generated.
     *
     * @param string $function_name the name of the PHP function to export.
     */
    public function exportPHPFunction($function_name)
    {
        $this->_export_list[] = $function_name;
    }

    // }}}
    // {{{ public function setRequestType()

    /**
     * Set the request type of this AJAX object
     *
     * TODO: This might be better on a per function level
     *
     * @param string $request_type the new request type to use.
     *
     * @return boolean true if the change was successful or false if the change
     *                  was unsuccessful. Changes are unsuccessful if the type
     *                  is not valid.
     */
    public function setRequestType($request_type)
    {
        $valid_types = array(HTTP_SAJAX_TYPE_GET, HTTP_SAJAX_TYPE_POST);

        $request_type = strtoupper($request_type);

        if (in_array($request_type, $valid_types)) {
            $this->_request_type = $request_type;
            return true;
        }

        return false;
    }

    // }}}
    // {{{ public function displayJavascript()

    /** 
     * Displays all javascript required by this AJAX object
     *
     * This calls all the private get*Javascript() methods.
     */
    public function displayJavascript()
    {
        static $shown = false;

        if ($shown === false) {
            echo $this->_getObjectJavascript();
            $shown = true;
        }

        // create javascript object
        echo "\tvar {$this->id} = new Sajax();\n\n";

        foreach ($this->_export_list as $function_name) {
            echo $this->_getFunctionStubJavascript($function_name);
        }
    }

    // }}}
    // {{{ public function handleClientRequest()

    /**
     * Handles a client request via HTTP.
     *
     * The handler checks to see if an exported function exists based on the
     * client request. If the exported function exists, this method executes
     * the function and wraps the result in an XML document and then sends the
     * XML document to the client.
     *
     * After the request has been handled, this function calls exit() to ensure
     * the request is finished.
     *
     * @return boolean false if the magic client request variable is not
     *                  present. If the magic request variable is present, this
     *                  method does not return at all.
     */
    public function handleClientRequest()
    {
        // look for magic client request variables to get request type
        if (!isset($_GET['rs']) && !isset($_POST['rs'])) {
            return false;
        }

        $this->_sendHeaders();

        $function_name = '';
        $function_args = array();

        switch ($this->_request_type) {
        case HTTP_SAJAX_TYPE_GET:

            if (isset($_GET['rs'])) {
                $function_name = $_GET['rs'];
            }
            if (isset($_GET['rsargs'])) {
                $function_args = $_GET['rsargs'];
            }

            break;

        case HTTP_SAJAX_TYPE_POST:

            if (isset($_POST['rs'])) {
                $function_name = $_POST['rs'];
            }
            if (isset($_POST['rsargs'])) {
                $function_args = $_POST['rsargs'];
            }

            break;
        }

        if (!in_array($function_name, $this->_export_list)) {

            $value = "The function '{$function_name}' is not callable.";
            $status = 'error';

        } elseif (!function_exists($function_name)) {

            $value = "The function '{$function_name}' does not exist.";
            $status = 'error';

        } else {

            $value = call_user_func_array($function_name, $function_args);
            $status = 'normal';

        }

        echo $this->_getResponseXML($value, $status);

        // end client request
        exit;
    }

    // }}}
    // {{{ private function _getResponseXML()

    /**
     * Gets the result of a PHP function or method wrapped in an XML document
     *
     * The XML document is well formed standalone XML. The XML document
     * contains the following elements:
     *
     * 1. response
     *
     * Response is the root element and all other elements are children of
     * the response element.
     *
     * 2. status
     *
     * The status is a string describing the status of the function or method
     * call. Valid values are "normal" and "error".
     *
     * 3. type
     *
     * The type indicates the variable type of the returned result. Valid
     * values are any of the builtin PHP types or an object class name.
     *
     * 4. value
     *
     * This is the value returned by the function or method. The value is
     * encapsulated in a CDATA section so that an XHTML string may be safely
     * returned by functions and methods.
     *
     * @param mixed $value the value of the function or method call.
     * @param string $status the status of the fucntion or method call.
     *
     * @return string a well formed XML document containg response information
     *                 of a PHP function or method call.
     */
    private function _getResponseXML($value, $status)
    {
        $xml =

        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<response>' .
        '<status>' . $status . '</status>' .
        '<type>' . gettype($value) . '</type>' .
        '<value><![CDATA[' . $value . ']]></value>' .
        '</response>';

        return $xml;
    }

    // }}}
    // {{{ private function _sendHeaders()

    /**
     * Sends headers disabling any form of caching and notifying the client
     * that is it receiving XML
     */
    private function _sendHeaders()
    {
        // Set content type to XML
        header('Content-type: text/xml');

        // Disable any caching with HTTP headers
        // Any date in the past will do here
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

        // Set always modified
        // for HTTP/1.1
        header('Cache-Control: no-cache, must-revalidate max-age=0');
        // for HTTP/1.0
        header('Pragma: no-cache');
    }

    // }}}
    // {{{ private function _getObjectJavascript()

    /**
     * Gets the javascript defining the Sajax javascript object
     *
     * The Sajax javascript object has everthing required to make asynchronous
     * XML HTTP requests in various browsers and has a method to handle the XML
     * result returned by the PHP server.
     *
     * The Sajax object is also able to display debug information to the client
     * if debug mode is enabled.
     *
     * @return string the javascript for the Sajax object.
     */
    private function _getObjectJavascript()
    {
        $javascript =

<<<JAVASCRIPT
        function Sajax()
        {
            this.debug_mode = false;
            this.request_uri = '{$this->remote_uri}';
            this.request_type = '{$this->_request_type}';
        }

        Sajax.prototype.getNewRequestObject = function()
        {
            var request_object = null;

            try {
                request_object = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (err1) {
                try {
                    request_object = new ActiveXObject('Microsoft.XMLHTTP');
                } catch (err2) {
                    request_object = null;
                }
            }

            if (!request_object && typeof XMLHttpRequest != 'undefined') {
                request_object = new XMLHttpRequest();
            }

            if (!request_object) {
                this.debug('Could not create connection object.');
            }

            return request_object;
        }

        Sajax.prototype.debug = function(text)
        {
            if (this.debug_mode) {
                alert('Sajax: ' + text);
            }
        }

        Sajax.prototype.callFunction = function(func_name, args)
        {
            var post_data, request_uri;

            request_uri = this.request_uri;

            // build client request
            if (this.request_type == 'GET') {

                if (request_uri.indexOf('?') == -1) {
                    request_uri = request_uri + '?rs=' + encodeURI(func_name);
                } else {
                    request_uri = request_uri + '&rs=' + encodeURI(func_name);
                }

                for (var i = 0; i < args.length - 1; i++) {
                    request_uri = request_uri + '&rsargs[]=' +
                        encodeURI(args[i]);
                }

                request_uri = request_uri + '&rsrnd=' +
                    new Date().getTime();

                post_data = null;

            } else {

                post_data = 'rs=' + encodeURI(func_name);
                for (var i = 0; i < args.length - 1; i++) {
                    post_data = post_data + '&rsargs[]=' + encodeURI(args[i]);
                }

            }

            var request_object = this.getNewRequestObject();

            request_object.open(this.request_type, request_uri, true);

            if (this.request_type == 'POST') {
                request_object.setRequestHeader('Method',
                    'POST ' + this.request_uri + ' HTTP/1.1');

                request_object.setRequestHeader('Content-Type',
                    'application/x-www-form-urlencoded');
            }

            // inside the anonymous function 'this' is not the Sajax object.
            var self = this;

            // server response handler
            request_object.onreadystatechange = function()
            {
                if (request_object.readyState != 4)
                    return;

                self.debug('received ' + request_object.responseText);

                var responseXML = request_object.responseXML;
                var childNodes = responseXML.documentElement.childNodes;

                var status, type, value;

                // extract data from XML document
                for (var i = 0; i < childNodes.length; i++) {
                    switch (childNodes[i].tagName) {
                    case 'status':
                        status = childNodes[i].firstChild.nodeValue;
                        break;
                    case 'type':
                        type = childNodes[i].firstChild.nodeValue;
                        break;
                    case 'value':
                        value = childNodes[i].firstChild.nodeValue;
                        break;
                    }
                }

                self.debug(status + ' : ' + type + ' : ' + value);

                if (status == 'error') {
                    alert('Error: ' + value);
                // the last argument should be a callback function
                } else if (typeof args[args.length - 1] == 'function') {
                    args[args.length - 1](value);
                }
            }

            // send client request
            request_object.send(post_data);

            this.debug(func_name + ' uri = ' + this.request_uri +
                '/post = ' + post_data);

            this.debug(func_name + ' waiting for response ...');

            // clean up request object
            delete request_object;
        }


JAVASCRIPT;

        return $javascript;
    }

    // }}}
    // {{{ private function _getFunctionStubJavascript()

    /**
     * Generates a method stub in javascript for a PHP function
     *
     * The method stub is a wrapper for the javascript callFunction()
     * method.
     *
     * @return string the javascript function stub for the given PHP function
     *                 name
     */
    private function _getFunctionStubJavascript($function_name)
    {
        $javascript =

<<<JAVASCRIPT
        {$this->id}.x_{$function_name} = function()
        {
            this.callFunction('{$function_name}', arguments);
        }


JAVASCRIPT;

        return $javascript;
    }

    // }}}
}

?>

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
 * functions from javascript without causing a full page request. This package
 * does not necessarily make use of the XML part of AJAX, it merely makes use
 * of the asynchronous javascript part. That said, it is certainly possible to
 * take advantage of XML with this package.
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
 * @link      http://pear.php.net/package/PackageName
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
     * object instance.
     *
     * @var string
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
    // {{{ public function setRequestType(0

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
            foreach ($this->_export_list as $function_name) {
                echo $this->_getFunctionStubJavascript($function_name);
            }

            $shown = true;
        }

        // create javascript object
        echo "var {$this->id} = new Sajax();\n";
    }

    // }}}
    // {{{ public function handleClientRequest()

    /**
     * Handles a client request via HTTP.
     *
     * The handler checks to see if an exported function exists based on the
     * client request and if so, it executes and returns the PHP function from
     * the server to the client.
     *
     * After the request has been handled, this function calls exit() to ensure
     * the request is finished.
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
            
        case HTTP_SAJAX_POST:

            if (isset($_POST['rs'])) {
                $function_name = $_POST['rs'];
            }
            if (isset($_POST['rsargs'])) {
                $function_args = $_POST['rsargs'];
            }

            break;
        }

        if (!in_array($function_name, $this->_export_list)) {
            echo "-:The function '{$function_name}' is not callable.";
        } else {
            echo '+:';
            // assume this returns a string
            echo call_user_func_array($function_name, $function_args);
        }

        // end client request
        exit;
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
    // {{{ private function _getInitObjectJavascript()

    /**
     * Gets the javascript for a Sajax javascript object
     *
     * The Sajax javascript object has everthing required to make asynchronous
     * XML HTTP requests object in various browsers and has a method to handle
     * the XML result returned by the PHP server.
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

            var request_object;

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
                this.request_object = null;
            } else {
                this.request_object = request_object;
            }
        }

        Sajax.prototype.debug = function(text)
        {
            if (this.debug_mode) {
                alert('RSD: ' + text);
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
            
            this.request_object.open(this.request_type, request_uri, true);

            if (this.request_type == 'POST') {
                this.request_object.setRequestHeader('Method',
                    'POST ' + this.request_uri + ' HTTP/1.1');

                this.request_object.setRequestHeader('Content-Type',
                    'application/x-www-form-urlencoded');
            }

            // inside the anonymous function 'this' is not the Sajax object.
            var self = this;
            
            // server response handler
            this.request_object.onreadystatechange = function()
            {
                if (self.request_object.readyState != 4)
                    return;

                self.debug('received ' + self.request_object.responseText);

                var status, data;
                status = self.request_object.responseText.charAt(0);
                data = self.request_object.responseText.substring(2);
                if (status == '-') {
                    alert('Error: ' + data);
                } else {
                    // the last argument should be a callback function
                    args[args.length-1](data);
                }
            }

            // send client request
            this.request_object.send(post_data);

            this.debug(func_name + ' uri = ' + this.request_uri +
                '/post = ' + post_data);
            
            this.debug(func_name + ' waiting for response ...');
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
        Sajax.prototype.x_{$function_name} = function()
        {
            this.callFunction('{$function_name}',
                this.x_{$function_name}.arguments);
        }

JAVASCRIPT;

        return $javascript;
    }

    // }}}
}

?>

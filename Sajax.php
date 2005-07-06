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
     * Defaults to {@link HTTP_SAJAX_TYPE_GET}.
     *
     * @var string
     */
    private $_request_type = '';

    // }}}
    // {{{ public function __construct()
    
    /**
     * Creates a new Simple AJAX object
     */
    public function __construct()
    {
        $this->_request_type = HTTP_SAJAX_TYPE_GET;
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
            if ($this->debug_mode) {
                echo $this->_getDebugJavascript();
            }
            echo $this->_getInitRequestObjectJavascript();
            echo $this->_getDoCallJavascript();

            foreach ($this->_export_list as $function_name) {
                echo $this->_getFunctionStubJavascript($function_name);
            }
            $shown = true;
        }
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
        if (isset($_GET['rs'])) {
            $mode = HTTP_SAJAX_TYPE_GET;
        } elseif (isset($_POST['rs'])) {
            $mode = HTTP_SAJAX_TYPE_POST;
        } else {
            return false;
        }

        $this->_sendHeaders();

        if ($mode == HTTP_SAJAX_TYPE_GET) {

            $function_name = $_GET['rs'];
            if (isset($_GET['rsargs'])) {
                $function_args = $_GET['rsargs'];
            } else {
                $function_args = array();
            }

        } else {

            $function_name = $_POST['rs'];
            if (isset($_POST['rsargs'])) {
                $function_args = $_POST['rsargs'];
            } else {
                $fuction_args = array();
            }

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
    // {{{ private fucntion _getDebugJavascript()

    /**
     * Gets the javascript that displays debug information to the client if
     * debug mode is enabled.
     *
     * @return string the javascript to display debug information to the
     *                 client.
     */
    private function _getDebugJavascript()
    {
        $javascript =

<<<JAVASCRIPT
        function sajax_debug(text) {
            alert('RSD: ' + text);
        }

JAVASCRIPT;

        return $javascript;
    }

    // }}}
    // {{{ private function _getInitRequestObjectJavascript()

    /**
     * Gets the javascript required to get a vaild XML HTTP request object in
     * various browsers
     *
     * @return string the javascript to get a valid XML HTTP request object in
     *                 various browsers.
     */
    private function _getInitRequestObjectJavascript()
    {
        $javascript =

<<<JAVASCRIPT
        function sajax_init_object() {

JAVASCRIPT;

        if ($this->debug_mode) {
            $javascript .=

<<<JAVASCRIPT
             sajax_debug('sajax_init_object() called..');

JAVASCRIPT;

        }

        $javascript .=

<<<JAVASCRIPT
            var request_object;
            try {
                request_object = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
                try {
                    request_object =
                        new ActiveXObject('Microsoft.XMLHTTP');
                } catch (oc) {
                    request_object = null;
                }
            }
            if (!request_object && typeof XMLHttpRequest != 'undefined')
                request_object = new XMLHttpRequest();

JAVASCRIPT;

        if ($this->debug_mode) {
            $javascript .=

<<<JAVASCRIPT
            if (!request_object)
                sajax_debug('Could not create connection object.');

JAVASCRIPT;

        }

        $javascript .=

<<<JAVASCRIPT
            return request_object;
        }

JAVASCRIPT;

        return $javascript;
    }

    // }}}
    // {{{ private function _getDoCallJavascript()

    /**
     * Gets the javascript to make asynchronous remote calls
     *
     * @return string the javascript necessary to make and receive asynchronous
     *                 remote calls.
     */
    private function _getDoCallJavascript()
    {
        // can't display constants in heredoc syntax
        $get = HTTP_SAJAX_TYPE_GET;
        $post = HTTP_SAJAX_TYPE_POST;

        $javascript = 

<<<JAVASCRIPT
        var sajax_request_type = '{$this->_request_type}';

        function sajax_do_call(func_name, args) {
            var i, request_object, n;
            var uri;
            var post_data;

            uri = '{$this->remote_uri}';

            // build client request
            if (sajax_request_type == '{$get}') {
        
                if (uri.indexOf('?') == -1)
                    uri = uri + '?rs=' + escape(func_name);
                else
                    uri = uri + '&rs=' + escape(func_name);
                for (i = 0; i < args.length-1; i++)
                    uri = uri + '&rsargs[]=' + escape(args[i]);
                uri = uri + '&rsrnd=' + new Date().getTime();
                post_data = null;

            } else {
        
                post_data = 'rs=' + escape(func_name);
                for (i = 0; i < args.length-1; i++)
                    post_data = post_data + '&rsargs[]=' + escape(args[i]);

            }
            
            request_object = sajax_init_object();
            request_object.open(sajax_request_type, uri, true);

            if (sajax_request_type == '{$post}') {
                request_object.setRequestHeader('Method',
                    'POST ' + uri + ' HTTP/1.1');

                request_object.setRequestHeader('Content-Type',
                    'application/x-www-form-urlencoded');
            }

            // server response handler
            request_object.onreadystatechange = function() {
                if (request_object.readyState != 4)
                    return;

JAVASCRIPT;

        if ($this->debug_mode) {
            $javascript .=

<<<JAVASCRIPT
                sajax_debug('received ' + request_object.responseText);

JAVASCRIPT;

        }

        $javascript .=

<<<JAVASCRIPT
                var status;
                var data;
                status = request_object.responseText.charAt(0);
                data = request_object.responseText.substring(2);
                if (status == '-')
                    alert('Error: ' + data);
                else
                    args[args.length-1](data);
            }

            // send client request
            request_object.send(post_data);

JAVASCRIPT;

        if ($this->debug_mode) {
            $javascript .=

<<<JAVASCRIPT
            sajax_debug(func_name + ' uri = ' +
                uri + '/post = ' + post_data);
            sajax_debug(func_name + ' waiting ...');

JAVASCRIPT;

        }

        $javascript .=

<<<JAVASCRIPT
            // clean up
            delete request_object;
        }

JAVASCRIPT;

        return $javascript;
    }

    // }}}
    // {{{ private function _getFunctionStubJavascript()

    /**
     * Generates a function stub in javascript for a PHP function
     *
     * The function stub is a wrapper for the javascript sajax_do_call()
     * function.
     *
     * @return string the javascript function stub for the given PHP function
     *                 name
     */
    private function _getFunctionStubJavascript($function_name)
    {
        $javascript =

<<<JAVASCRIPT
        // wrapper for  {$function_name}
        function x_{$function_name}() {
            sajax_do_call('{$function_name}',
                x_{$function_name}.arguments);
        }

JAVASCRIPT;

        return $javascript;
    }

    // }}}
}

?>

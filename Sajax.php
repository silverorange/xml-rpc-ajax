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
		if ($this->debug_mode) {
			echo $this->_getDebugJavascript();
		}
		echo $this->_getInitRequestObjectJavascript();
		echo $this->_getDoCallJavascript();

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

		"function sajax_debug(text) {\n" .
		"	alert('RSD: ' + text);\n" .
		"}\n";

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

		"function sajax_init_object() {\n";
		
		if ($this->debug_mode) {
			$javascript .=
 		"	sajax_debug('sajax_init_object() called..')\n";
		}
		
		$javascript .=

 		"	var request_object;\n" .
		"	try {\n" .
		"		request_object = new ActiveXObject('Msxml2.XMLHTTP');\n" .
		"	} catch (e) {\n" .
		"		try {\n" .
		"			request_object =\n" .
		"				new ActiveXObject('Microsoft.XMLHTTP');\n" .
		"		} catch (oc) {\n" .
		"			request_object = null;\n" .
		"		}\n" .
		"	}\n" .
		"	if (!request_object && typeof XMLHttpRequest != 'undefined')\n" .
		"		request_object = new XMLHttpRequest();\n";
		
		if ($this->debug_mode) {
			$javascript .=
		"	if (!request_object)\n" .
		"		sajax_debug('Could not create connection object.');\n";
		}

		$javascript .=
		
		"	return request_object;\n" .
		"}\n";

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
		$javascript = 

		"var sajax_request_type = '{$this->_request_type}';\n" .

		"function sajax_do_call(func_name, args) {\n" .
		"	var i, request_object, n;\n" .
		"	var uri;\n" .
		"	var post_data;\n" .

		"	uri = '{$this->remote_uri}';\n" .

		// build client request
		"	if (sajax_request_type == '" . HTTP_SAJAX_TYPE_GET . "') {\n" .
		
		"		if (uri.indexOf('?') == -1)\n" .
		"			uri = uri + '?rs=' + escape(func_name);\n" .
		"		else\n" .
		"			uri = uri + '&rs=' + escape(func_name);\n" .
		"		for (i = 0; i < args.length-1; i++)\n" .
		"			uri = uri + '&rsargs[]=' + escape(args[i]);\n" .
		"		uri = uri + '&rsrnd=' + new Date().getTime();\n" .
		"		post_data = null;\n" .

		"	} else {\n" .
		
		"		post_data = 'rs=' + escape(func_name);\n" .
		"		for (i = 0; i < args.length-1; i++)\n" .
		"			post_data = post_data + '&rsargs[]=' + escape(args[i]);\n" .
		
		"	}\n" .
			
		"	request_object = sajax_init_object();\n" .
		"	request_object.open(sajax_request_type, uri, true);\n" .
		
		"	if (sajax_request_type == '" . HTTP_SAJAX_TYPE_POST . "') {\n" .
		"		request_object.setRequestHeader('Method',\n" .
		"			'POST ' + uri + ' HTTP/1.1');\n" .
		
		"		request_object.setRequestHeader('Content-Type',\n" .
		"			'application/x-www-form-urlencoded');\n" .
		"	}\n" .

		// server response handler
		"	request_object.onreadystatechange = function() {\n" .
		"		if (request_object.readyState != 4)\n" .
		"			return;\n";

		if ($this->debug_mode) {
			$javascript .=
		"		sajax_debug('received ' + request_object.responseText);\n";
		}
		
		$javascript .=
				
		"		var status;\n" .
		"		var data;\n" .
		"		status = request_object.responseText.charAt(0);\n" .
		"		data = request_object.responseText.substring(2);\n" .
		"		if (status == '-')\n" .
		"			alert('Error: ' + data);\n" .
		"		else\n" .
		"			args[args.length-1](data);\n" .
		"	}\n" .

		// send client request
		"	request_object.send(post_data);\n";
		
		if ($this->debug_mode) {
			$javascript .=
		"	sajax_debug(func_name + ' uri = ' +\n" .
		"		uri + '/post = ' + post_data);\n" .
		"	sajax_debug(func_name + ' waiting ...');\n";
		}

		$javascript .=

		// clean up
		"	delete request_object;\n" .
		"}\n";

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

		"// wrapper for  {$function_name}\n" .
		"function x_{$function_name}() {\n" .
		"	sajax_do_call('{$function_name}',\n" .
		"		x_{$function_name}.arguments);\n" .
		"}\n";

		return $javascript;
	}

	// }}}
}

?>

/**
 * An XML-RPC client
 *
 * The client uses asynchronous HTTP requests to make procedure calls on the
 * server.
 *
 * @param string server the uri of the XML-RPC server.
 */
function XML_RPC_Client(server)
{
	this.request_uri = server;
}

/**
 * Gets a new HTTP request object
 *
 * The HTTP request object is what is used to make XML-RPC client requests.
 * This methods gets an appropriate request object for most modern browsers.
 * If the browser is not supported, an exception is thrown.
 *
 * @return object a new HTTP request object.
 *
 * @throws XML_RPC_Exception
 */
XML_RPC_Client.prototype.getNewRequestObject = function()
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
		throw new XML_RPC_Exception(0, 'XML_RPC_Client: Could not ' +
			'create connection object.');
	}

	return request_object;
}

/**
 * Calls a remote procedure on the XML-RPC server
 *
 * @param string procedure_name the name of the procedure to run on the server.
 * @param Array procedure_arguments an array of arguments to pass to the
 *                                   procedure being called.
 * @param function callback a function that should be called when the XML-RPC
 *                           server responds to this client request.
 */
XML_RPC_Client.prototype.callProcedure = function(procedure_name,
	procedure_arguments, callback)
{
	var xml_rpc_request = new XML_RPC_Request(procedure_name,
		procedure_arguments);

	var post_data = xml_rpc_request.marshall();
	alert(post_data);
	var request_object = this.getNewRequestObject();

	// open an asynchronous HTTP connection to the XML-RPC server
	request_object.open('POST', this.request_uri, true);

	// send appropriate headers
	request_object.setRequestHeader('Method',
		'POST ' + this.request_uri + ' HTTP/1.1');

	request_object.setRequestHeader('User-Agent:', 'XML-RPC Javascript');
	request_object.setRequestHeader('Content-Type', 'text/xml');
	request_object.setRequestHeader('Content-length', post_data.length);

	// server response handler
	request_object.onreadystatechange = function()
	{
		// check if request is finished
		if (request_object.readyState == 4) {
			try {
				alert(request_object.responseText);
				var response =
					new XML_RPC_Response(request_object.responseXML);
			
				// the last argument should be a callback function
				if (typeof callback == 'function') {
					// call the callback with the response value
					callback(response.getValue());
				}
			} catch (e) {
				// the server send back malformed XML.
				// silently die
			}
		}
	}

	// send client request
	request_object.send(post_data);

	// clean up request object
	delete request_object;
}

/**
 * An XML-RPC client
 *
 * The client uses asynchronous HTTP requests to make procedure calls on the
 * server.
 *
 * @param string server the uri of the XML-RPC server.
 *
 * @throws XML_RPC_Exception
 */
function XML_RPC_Client(server)
{
	// Opera does not automatically use the document base href when opening
	// a request with the XMLHttpRequest object. This grabs the base href
	// from the document.
	if (navigator.userAgent.indexOf('Opera') != -1) {
		var base_tags = document.getElementsByTagName('base');
		if (base_tags.length > 0) {
			var base_href = base_tags[0].getAttribute('href');
		} else {
			throw new XML_RPC_Exception(0, 'XML_RPC_Client: Could not find ' +
				'document base href for Opera.');
		}
	} else {
		var base_href = '';
	}

	this.request_uri = base_href + server;
}

/**
 * Turns debugging on or off
 *
 * Defaults to off.
 *
 * @var boolean
 * @static
 */
XML_RPC_Client.debug = false;

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
 * @param function callback a function that should be called when the XML-RPC
 *                           server responds to this client request.
 * @param Array procedure_arguments an array of arguments to pass to the
 *                                   procedure being called.
 * @param Array procedure_types an optional array of XML-RPC types to use for
 *                                  the procedure arguments.
 */
XML_RPC_Client.prototype.callProcedure = function(procedure_name, callback,
	procedure_arguments, procedure_types)
{
	// Check if arguments were passed as an array.
	if (!(procedure_arguments instanceof Array))
		procedure_arguments = [procedure_arguments];

	// Check if types were passed.
	if (arguments.length > 3) {
		// Check if types were passed as an array.
		if (!(procedure_types instanceof Array))
			procedure_types = [procedure_types];

		var xml_rpc_request = new XML_RPC_Request(procedure_name,
			procedure_arguments, procedure_types);
	} else {
		var xml_rpc_request = new XML_RPC_Request(procedure_name,
			procedure_arguments);
	}

	var post_data = xml_rpc_request.marshall();
	var request_object = this.getNewRequestObject();

	// open an asynchronous HTTP connection to the XML-RPC server
	request_object.open('POST', this.request_uri, true);

	// send appropriate headers
	request_object.setRequestHeader('Method',
		'POST ' + this.request_uri + ' HTTP/1.1');

	request_object.setRequestHeader('User-Agent', 'XML-RPC Javascript');
	request_object.setRequestHeader('Content-Type', 'text/xml');

	// server response handler
	request_object.onreadystatechange = function()
	{
		// check if request is finished
		if (request_object.readyState == 4) {

			// debug
			if (XML_RPC_Client.debug)
				alert(request_object.responseText);

			var response =
				new XML_RPC_Response(request_object.responseXML);
		
			// the last argument should be a callback function
			if (typeof callback == 'function') {
				// call the callback with the response value
				callback(response.getValue());
			}
		}
	}

	// send client request
	request_object.send(post_data);

	// clean up request object
	delete request_object;
}

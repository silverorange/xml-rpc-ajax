/**
 * An XML-RPC client
 *
 * The client uses asynchronous HTTP requests to make procedure calls on the
 * server.
 *
 * @param string request_uri the uri of the XML-RPC server.
 *
 * @throws XML_RPC_Exception
 */
function XML_RPC_Client(request_uri)
{
	this.request_uri = request_uri;
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

	if (XML_RPC_Client.debug)
		alert('XML_RPC_Client: Sending request:\n' + post_data);

	var request_callback = {
		success: this.handleSuccessfulResponse,
		failure: this.handleFailedResponse,
		scope: this,
		argument: callback
	};

	// send appropriate headers
	YAHOO.util.Connect.setDefaultPostHeader(false);
	YAHOO.util.Connect.initHeader('Content-Type', 'text/xml');
	YAHOO.util.Connect.initHeader('User-Agent', 'XML-RPC Javascript');

	// open an asynchronous HTTP connection to the XML-RPC server
	var request = YAHOO.util.Connect.asyncRequest('POST', this.request_uri,
		request_callback, post_data);
}

XML_RPC_Client.prototype.handleSuccessfulResponse = function(o)
{
	// debug
	if (XML_RPC_Client.debug)
		alert('XML_RPC_Client: Request successful:\n' + o.responseText);

	var xml_rpc_response = new XML_RPC_Response(o.responseXML);

	// the last argument should be a callback function
	if (typeof o.argument == 'function') {
		// call the callback with the response value
		o.argument(xml_rpc_response.getValue());
	}
}

XML_RPC_Client.prototype.handleFailedResponse = function(o)
{
	// debug
	if (XML_RPC_Client.debug)
		alert('XML_RPC_Client: Request failed:\n' + o.responseText);
}

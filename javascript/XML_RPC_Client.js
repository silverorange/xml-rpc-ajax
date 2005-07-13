function XML_RPC_Client(server)
{
	this.request_uri = server;
}

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
		alert('XML-RPC Client: Error could not create connection object.');
	}

	return request_object;
}

XML_RPC_Client.prototype.callXmlRpcProcedure = function(procedure_name, args)
{

	var request_uri = this.request_uri;
	var xml_rpc_request = new XML_RPC_Request(procedure_name, args);
	var post_data = xml_rpc_request.toString();
	var request_object = this.getNewRequestObject();

	request_object.open('POST', request_uri, true);

	request_object.setRequestHeader('Method',
		'POST ' + this.request_uri + ' HTTP/1.1');

	request_object.setRequestHeader('User-Agent:', 'HTTP_Sajax');
	request_object.setRequestHeader('Content-Type', 'text/xml');
	request_object.setRequestHeader('Content-length', post_data.length);

	// inside the literal function 'this' is not the XML_RPC_Client object.
	var self = this;

	// server response handler
	request_object.onreadystatechange = function()
	{
		if (request_object.readyState != 4)
			return;

		var response =
			new XML_RPC_Response(request_object.responseXML);
		
		// the last argument should be a callback function
		if (typeof args[args.length - 1] == 'function') {
			args[args.length - 1](response.getValue());
		}
	}

	// send client request
	request_object.send(post_data);

	// clean up request object
	delete request_object;
}

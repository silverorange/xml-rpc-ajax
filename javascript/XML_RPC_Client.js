function XML_RPC_Client(server)
{
	this.debug_mode = false;
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
		this.debug('Could not create connection object.');
	}

	return request_object;
}

XML_RPC_Client.prototype.debug = function(text)
{
	if (this.debug_mode) {
		alert('XML-RPC Client: ' + text);
	}
}

XML_RPC_Client.prototype.callXmlRpcProcedure = function(procedure_name, args)
{
	var post_data, request_uri;

	request_uri = this.request_uri;

	// build client request
	post_data = '<' + '?xml version="1.0" encoding="UTF-8"?' + '>\n' + 
		'<methodCall>\n' +
		'<methodName>' + procedure_name + '</methodName>\n' +
		'<params>\n<param><value>';

	for (var i = 0; i < args.length - 1; i++) {
		if (i < args.length - 2) {
			post_data = post_data + XHTML_Escaper.escape(args[i]) +
				'</value></param>\n<param><value>';
		} else {
			post_data = post_data + XHTML_Escaper.escape(args[i]);
		}
	}

	post_data = post_data + '</value></param>\n</params>\n' +
		'</methodCall>';

	this.debug(post_data);

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

		self.debug('received ' + request_object.responseText);

		self.debug(request_object.responseText);

		var response =
			new XML_RPC_Response(request_object.responseXML);
		
		self.debug(response.hasFault() + ' : ' + response.getValue());

		// the last argument should be a callback function
		if (typeof args[args.length - 1] == 'function') {
			args[args.length - 1](response.getValue());
		}
	}

	// send client request
	request_object.send(post_data);

	this.debug(procedure_name + ' uri = ' + this.request_uri +
		'/post = ' + post_data);

	this.debug(procedure_name + ': waiting for response ...');

	// clean up request object
	delete request_object;
}

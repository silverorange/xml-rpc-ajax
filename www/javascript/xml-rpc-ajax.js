function XML_RPC_Exception(number, message)
{
	this.number = number;
	this.message = message;
}

XML_RPC_Exception.prototype = new Error;

function XML_RPC_Date(value)
{
	this.value = value;
}

/**
 *
 * YYYYMMDDTHH:MM:SS+HHMM as per the XML-RPC specification
 */
XML_RPC_Date.prototype.marshall = function()
{
	function padZeros(number)
	{
		return (number < 10) ? '0' + number : number;
	}
	var timezoneOffsetSign = (this.value.getTimezoneOffset() < 0) ? '-' : '+';
	var timezoneOffsetHours = Math.floor(this.value.getTimezoneOffset() / 60)
	var timezoneOffsetMinutes = this.value.getTimezoneOffset() -
		(timezoneOffsetHours * 60);

	var xml = '<dateTime.iso8601>' +
		this.value.getFullYear() +
		padZeros(this.value.getMonth() + 1) +
		padZeros(this.value.getDate()) +
		'T' +
		padZeros(this.value.getHours()) + ':' +
		padZeros(this.value.getMinutes()) + ':' +
		padZeros(this.value.getSeconds()) +
		timezoneOffsetSign +
		padZeros(timezoneOffsetHours) +
		padZeros(timezoneOffsetMinutes) +
		'</dateTime.iso8601>';

	return xml;
}

XML_RPC_Date.unmarshall = function(date_node)
{
	var date_value = new Date();
	// TODO: parse iso8601 date here
	return date_value;
}

/**
 * An array XML-RPC type
 *
 * @param Array value the array this XML-RPC type represents.
 */
function XML_RPC_Array(value)
{
	this.value = value;
}

/**
 * Returns this XML-RPC type as a well formed XML fragment
 *
 * @return string this XML-RPC type as a well formed XML fragment.
 */
XML_RPC_Array.prototype.marshall = function()
{
	var value;
	var xml = '\n<array><data>\n';

	for (var i = 0; i < this.value.length; i++) {
		value = XML_RPC_Request.getXmlRpcValue(this.value[i]);

		xml = xml + '<value>' + value.marshall() + '</value>\n';
	}

	xml = xml + '</data></array>\n';

	return xml;
}

XML_RPC_Array.unmarshall = function(array_node)
{
	var array_value = new Array();
	var array_subnodes = array_node.childNodes;
	for (var i = 0; i < array_subnodes.length; i++) {
		if (array_subnodes[i].nodeName == 'data') {
			var data_subnodes = array_subnodes[i].childNodes;
			for (var j = 0; j < data_subnodes.length; j++) {
				if (data_subnodes[j].nodeName == 'value') {
					var data_value =
						XML_RPC_Response.parseValueNode(data_subnodes[j]);

					array_value.push(data_value);
				}
			}
			break;
		}
	}

	return array_value;
}

/**
 * A string XML-RPC type
 *
 * @param string value the string this XML-RPC type represents.
 */
function XML_RPC_String(value)
{
	this.value = new String(value);
}

/**
 * Returns this XML-RPC type as a well formed XML fragment
 *
 * @return string this XML-RPC type as a well formed XML fragment.
 */
XML_RPC_String.prototype.marshall = function()
{
	var value = this.value;
	value = value.replace(/&/g, '&amp;');
	value = value.replace(/</g, '&lt;');
	var xml = '<string>' + value + '</string>';

	return xml;
}

XML_RPC_String.unmarshall = function(string_node)
{
	var string_value = '';

	// Some DOM implementations split large strings into multiple text nodes.
	for (var i = 0; i < string_node.childNodes.length; i++) {
		if (string_node.childNodes[i].nodeType == 3) {
			string_value += string_node.childNodes[i].nodeValue;
		}
	}

	return string_value;
}

/**
 * A double floating-point number XML-RPC type
 *
 * @param number value the number this XML-RPC type represents.
 */
function XML_RPC_Double(value)
{
	switch (value) {
	case Infinity:
	case NaN:
	case Number.NaN:
	case Number.POSITIVE_INFINITY:
	case Number.NEGATIVE_INFINITY:
		throw new XML_RPC_Exception(0, 'XML_RPC_Double: Cannot convert NaN ' +
			'or Infinity to corresponding XML-RPC data types.');

		break;
	default:
		this.value = value;
		break;
	}
}

/**
 * Returns this XML-RPC type as a well formed XML fragment
 *
 * @return string this XML-RPC type as a well formed XML fragment.
 */
XML_RPC_Double.prototype.marshall = function()
{
	var xml = '<double>' + this.value + '</double>';

	return xml;
}

XML_RPC_Double.unmarshall = function(double_node)
{
	var value = double_node.firstChild.nodeValue;
	var double_value = parseFloat(value);
	return double_value;
}

/**
 * A 4-byte integer XML-RPC type
 *
 * @param number value the number this XML-RPC type represents.
 */
function XML_RPC_Int(value)
{
	var int_value = (value > 0) ? Math.floor(value) : Math.ceil(value);

	if (int_value != value)
		throw new XML_RPC_Exception(0, 'XML_RPC_Int: Value is not an ' +
			'integer.');

	this.value = int_value;
}

/**
 * Returns this XML-RPC type as a well formed XML fragment
 *
 * @return string this XML-RPC type as a well formed XML fragment.
 */
XML_RPC_Int.prototype.marshall = function()
{
	var xml = '<int>' + this.value + '</int>';

	return xml;
}

XML_RPC_Int.unmarshall = function(int_node)
{
	var value = int_node.firstChild.nodeValue;
	var int_value = parseInt(value);
	return int_value;
}

/**
 * A boolean XML-RPC type
 *
 * @param boolean value the booean this XML-RPC type represents.
 */
function XML_RPC_Boolean(value)
{
	this.value = (value == true) ? true : false;
}

/**
 * Returns this XML-RPC type as a well formed XML fragment
 *
 * @return string this XML-RPC type as a well formed XML fragment.
 */
XML_RPC_Boolean.prototype.marshall = function()
{
	var int_value = (this.value) ? 1 : 0;
	var xml = '<boolean>' + int_value + '</boolean>';

	return xml;
}

XML_RPC_Boolean.unmarshall = function(boolean_node)
{
	var value = boolean_node.firstChild.nodeValue;
	var boolean_value = (parseInt(value)) ? true : false;
	return boolean_value;
}

/**
 * A struct XML-RPC type
 *
 * @param Object value the Object this XML-RPC type represents.
 */
function XML_RPC_Struct(value)
{
	this.value = value;
}

/**
 * Returns this XML-RPC type as a well formed XML fragment
 *
 * @return string this XML-RPC type as a well formed XML fragment.
 */
XML_RPC_Struct.prototype.marshall = function()
{
	var value;
	var xml = '<struct>\n';
	for (var member in this.value) {
		value = XML_RPC_Request.getXmlRpcValue(this.value[member]);

		xml = xml + '<member>\n<name>' + member + '</name>\n' +
			'<value>' + value.marshall() + '</value>\n</member>\n';
	}
	xml = xml + '</struct>\n';

	return xml;
}

XML_RPC_Struct.unmarshall = function(struct_node)
{
	var struct_value = new Object();
	var struct_subnodes = struct_node.childNodes;
	for (var i = 0; i < struct_subnodes.length; i++) {
		if (struct_subnodes[i].nodeName == 'member') {
			var member_subnodes = struct_subnodes[i].childNodes;
			for (var j = 0; j < member_subnodes.length; j++) {
				if (member_subnodes[j].nodeName == 'name') {
					var member_name = member_subnodes[j].firstChild.nodeValue;
				} else if (member_subnodes[j].nodeName == 'value') {
					var member_value =
						XML_RPC_Response.parseValueNode(member_subnodes[j]);
				}
			}
			struct_value[member_name] = member_value;
		}
	}

	return struct_value;
}

/**
 * An XML-RPC request object
 *
 * @param string procedure_name the name of the remote procedure of this
 *                               request
 * @param Array procedure_arguments the paramaters of the remote procedure
 *                                   call.
 */
function XML_RPC_Request(procedure_name, procedure_arguments, procedure_types)
{
	this.procedure_name = procedure_name;
	this.procedure_arguments = procedure_arguments;
	this.procedure_types = (arguments.length > 2) ? arguments[2] : null;
}

/**
 * Gets this request as a well formed XML object
 *
 * @return string this request as a well formed XML object.
 */
XML_RPC_Request.prototype.marshall = function()
{
	var parameter;
	var xml = '<' + '?xml version="1.0" encoding="UTF-8"?' + '>\n' +
		'<methodCall>\n' +
		'<methodName>' + this.procedure_name + '</methodName>\n' +
		'<params>\n';

	for (var i = 0; i < this.procedure_arguments.length; i++) {
		parameter = this.getParameter(i);
		xml = xml + '<param><value>' + parameter.marshall() + '</value></param>\n';
	}

	xml = xml + '</params>\n' +
		'</methodCall>';

	return xml;
}

/**
 * Gets a new XML-RPC value object for a parameter of this request
 *
 * @param number parameter_number the position of the parameter to get. This is
 *                                 zero based.
 *
 * @return mixed a new XML-RPC object representing the value.
 *
 * @throws XML_RPC_Exception
 */
XML_RPC_Request.prototype.getParameter = function(parameter_number)
{
	var new_value;
	var value = this.procedure_arguments[parameter_number];

	if (this.procedure_types instanceof Array &&
		this.procedure_types.length > parameter_number)
		new_value = XML_RPC_Request.getXmlRpcValue(value,
			this.procedure_types[parameter_number]);
	else
		new_value = XML_RPC_Request.getXmlRpcValue(value);

	return new_value;
}

/**
 * Gets a new XML-RPC value object for a JavaScript value
 *
 * This method is static.
 *
 * @param mixed value the JavaScript value to get the XML-RPC value for.
 * @param String type an optional type of the value. If no type is specified,
 *                     the type is detected from the JavaScript type.
 *
 * @return mixed a new XML-RPC object representing the value.
 *
 * @throws XML_RPC_Exception
 */
XML_RPC_Request.getXmlRpcValue = function(value, type)
{
	var new_value;
	if (arguments.length < 2)
		var type = XML_RPC_Request.getType(value);

	switch (type) {
	case 'string':
		new_value = new XML_RPC_String(value);
		break;
	case 'boolean':
		new_value = new XML_RPC_Boolean(value);
		break;
	case 'int':
		new_value = new XML_RPC_Int(value);
		break;
	case 'double':
		new_value = new XML_RPC_Double(value);
		break;
	case 'array':
		new_value = new XML_RPC_Array(value);
		break;
	case 'date':
		new_value = new XML_RPC_Date(value);
		break;
	case 'struct':
		new_value = new XML_RPC_Struct(value);
		break;
	default:
		throw new XML_RPC_Exception(0, 'XML_RPC_Request: Invalid XML-RPC ' +
			'type "' + type + '" in request.');
	}

	return new_value;
}

/**
 * Gets an XML-RPC type from a JavaScript variable
 *
 * The list of XML-RPC types is at http://www.xmlrpc.com/spec. JavaScript types
 * do not may one-to-one with XML-RPC types. In these cases, a best guess is
 * made.
 *
 * If your application has strict type requirements, you should specify the
 * XML-RPC types when creating a new request.
 *
 * This is a static method.
 *
 * @param mixed value the value to get an XML-RPC type for.
 *
 * @return string the XML-RPC type of the JavaScript variable.
 */
XML_RPC_Request.getType = function(value)
{
	switch (typeof value) {
	case 'string':
		return 'string';
	case 'boolean':
		return 'boolean';
	case 'number':
		// this is a best guess
		return 'double';
	// both array and objects are 'object'
	case 'object':
		if (value instanceof Array)
			return 'array';

		if (value instanceof Date)
			return 'date';

		return 'struct';
	default:
		throw new XML_RPC_Exception(0, 'XML_RPC_Request: Cannot convert a ' +
			'value of type ' + typeof value + ' to a valid XML-RPC ' +
			'request type.');
	}
}

/**
 * A XML-RPC response from the server
 *
 * This object parses the response object and then makes several utility
 * methods available based on the response data.
 *
 * @param DOMDocument response_xml the document object representing the XML
 *                                  document returned by the XML-RPC server.
 *
 * @throws XML_RPC_Exception
 */
function XML_RPC_Response(response_xml)
{
	var response_document = response_xml.documentElement;
	this.response_document = response_document;

	// make sure we received an XML-RPC response
	if (response_document.tagName != 'methodResponse') {
		throw new XML_RPC_Exception(0, "XML_RPC_Response: Result is not a " +
			"'methodResponse'. Received a '" +
			response_document.tagName + "'");
	}

	var child_nodes = response_document.childNodes;
	var value_node = null;

	// get top value node from XML document
	for (var i = 0; i < child_nodes.length; i++) {
		// check if we got a params or a fault
		switch (child_nodes[i].nodeName) {
		case 'params':
			this.has_fault = false;
			var params_nodes = child_nodes[i].childNodes;
			for (var j = 0; j < params_nodes.length; j++) {
				if (params_nodes[j].tagName == 'param') {
					var param_nodes = params_nodes[j].childNodes;
					for (var k = 0; k < param_nodes.length; k++) {
						if (param_nodes[k].nodeName == 'value') {
							value_node = param_nodes[k];
							break;
						}
					}
					break;
				}
			}
			break;
		case 'fault':
			this.has_fault = true;
			var fault_nodes = child_nodes[i].childNodes;
			for (var j = 0; j < fault_nodes.length; j++) {
				if (fault_nodes[j].nodeName == 'value') {
					value_node = fault_nodes[j];
					break;
				}
			}
			break;
		}
	}

	if (value_node == null) {
		throw new XML_RPC_Exception(0, 'XML_RPC_Response: Malformed ' +
			'response. No value node is present in the response.');
	}

	this.value = XML_RPC_Response.parseValueNode(value_node);

	if (this.has_fault) {
		this.fault_code = this.value.faultCode;
		this.fault_message = this.value.faultString;
	} else {
		this.fault_code = -1;
		this.fault_message = '';
	}
}

/**
 * Gets the javascript value of this response
 *
 * @return mixed the javascript value of this response.
 */
XML_RPC_Response.prototype.getValue = function()
{
	return this.value;
}

/**
 * Gets the fault code of this response
 *
 * @return integer the fault code of this response or -1 if it there is no
 *                  fault.
 */
XML_RPC_Response.prototype.getFaultCode = function()
{
	return this.fault_code;
}

/**
 * Gets the fault message of this response
 *
 * @return strong the fault message or a blank string if there is no fault.
 */
XML_RPC_Response.prototype.getFaultMessage = function()
{
	return this.fault_message;
}

/**
 * Returns true if this reponse has a fault
 *
 * @return boolean true if this response has a fault, false otherwise.
 */
XML_RPC_Response.prototype.hasFault = function()
{
	return this.has_fault;
}

/**
 * Parses an XML value node into an appropriate javascript object
 *
 * DOM Level 2 Traverasl is not supported in any major browsers other than
 * Opera at the moment so this method uses the childNodes[] property to parse
 * nodes.
 *
 * @param Node node the XML value node to parse.
 *
 * @return mixed an appropriate javascript object representing the value.
 */
XML_RPC_Response.parseValueNode = function(value_node)
{
	var value = null;
	var child_nodes = value_node.childNodes;

	if (child_nodes.length == 0) {
		// blank string is the only thing this can be
		value = '';
	} else {
		for (var i = 0; i < child_nodes.length; i++) {
			switch (child_nodes[i].nodeName) {
			case 'int':
			case 'i4':
				value = XML_RPC_Int.unmarshall(child_nodes[i]);
				break;

			case 'double':
				value = XML_RPC_Double.unmarshall(child_nodes[i]);
				break;

			case 'base64':
				// TODO: not implemented yet.
				value = null;
				break;

			case 'boolean':
				value = XML_RPC_Boolean.unmarshall(child_nodes[i]);
				break;

			case 'string':
				value = XML_RPC_String.unmarshall(child_nodes[i]);
				break;

			case 'dateTime.iso8601':
				value = XML_RPC_Date.unmarshall(child_nodes[i]);
				break;

			case 'array':
				value = XML_RPC_Array.unmarshall(child_nodes[i]);
				break;

			case 'struct':
				value = XML_RPC_Struct.unmarshall(child_nodes[i]);
				break;

			case '#text':
				// ignore until later
				break;

			default:
				throw new XML_RPC_Exception(0, 'XML_RPC_Response: unknown ' +
					'XML-RPC data type encountered.');
			}
		}

		// assume string
		if (value == null) {
			value = XML_RPC_String.unmarshall(value_node);
		}
	}

	return value;
}

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
	this.setRequestUri(request_uri);
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

XML_RPC_Client.prototype.setRequestUri = function(uri)
{
	// check for relative URI
	if (uri.search(/^https?:\/\//) != 0) {
		// looks for base href
		var bases = document.getElementsByTagName('base');
		if (bases.length > 0 && bases[0].href) {
			var base = bases[0].href;
			if (base.charAt(base.length - 1) != '/') {
				base += '/';
			}
			if (uri.charAt(0) == '/') {
				uri = uri.substring(1);
			}
			uri = base + uri;
		}
	}

	if (XML_RPC_Client.debug)
		alert('XML_RPC_Client: Request URI:\n' + uri);

	this.request_uri = uri;
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
	YAHOO.util.Connect.initHeader('User-Agent', window.navigator.userAgent);

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

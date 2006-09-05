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
	var type;
	var value = this.procedure_arguments[parameter_number];

	if (this.procedure_types instanceof Array &&
		this.procedure_types.length > parameter_number)
		type = this.procedure_types[parameter_number];
	else
		type = XML_RPC_Request.getType(value);

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

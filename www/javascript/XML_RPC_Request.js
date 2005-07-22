/**
 * An XML-RPC request object
 *
 * @param string procedure_name the name of the remote procedure of this
 *                               request
 * @param Array procedure_arguments the paramaters of the remote procedure
 *                                   call.
 */
function XML_RPC_Request(procedure_name, procedure_arguments)
{
	this.procedure_name = procedure_name;
	this.procedure_arguments = procedure_arguments;
}

/**
 * Gets this request as a well formed XML object
 *
 * @return string this request as a well formed XML object.
 */
XML_RPC_Request.prototype.marshall = function()
{
	var value;
	var xml = '<' + '?xml version="1.0" encoding="UTF-8"?' + '>\n' + 
		'<methodCall>\n' +
		'<methodName>' + this.procedure_name + '</methodName>\n' +
		'<params>\n';

	for (var i = 0; i < this.procedure_arguments.length; i++) {
		value = XML_RPC_Request.getNewValue(this.procedure_arguments[i]);

		xml = xml + '<param><value>' + value.marshall() + '</value></param>\n';
	}

	xml = xml + '</params>\n' +
		'</methodCall>';

	return xml;
}

/**
 * Gets a new XML-RPC value object based on javascript variable type
 *
 * This is a static method.
 *
 * @param mixed value the javascript value to get an XML-RPC value object for.
 *
 * @return mixed a new XML-RPC object representing the value.
 */
XML_RPC_Request.getNewValue = function(value)
{
	var new_value;
	
	switch (typeof value) {
	case 'string':
		new_value = new XML_RPC_String(value);
		break;
	case 'boolean':
		new_value = new XML_RPC_Boolean(value);
		break;
	case 'number':
		new_value = new XML_RPC_Double(value);
		break;
	// both array and objects are 'object'
	case 'object':
		if (value instanceof Array) {
			new_value = new XML_RPC_Array(value);
			break;
		} else {
			new_value = new XML_RPC_Struct(value);
			break;
		}
	}

	return new_value;
}

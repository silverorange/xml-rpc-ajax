function XML_RPC_Request(procedure_name, arguments)
{
	this.procedure_name = procedure_name;
	this.arguments = arguments;
}

XML_RPC_Request.prototype.toString = function()
{
	var value;
	var xml = '<' + '?xml version="1.0" encoding="UTF-8"?' + '>\n' + 
		'<methodCall>\n' +
		'<methodName>' + this.procedure_name + '</methodName>\n' +
		'<params>\n';

	for (var i = 0; i < this.arguments.length - 1; i++) {
		value = XML_RPC_Request.getNewValue(this.arguments[i]);

		xml = xml + '<param><value>' + value.toString() + '</value></param>\n';
	}

	xml = xml + '</params>\n' +
		'</methodCall>';

	alert(xml);
	return xml;
}

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

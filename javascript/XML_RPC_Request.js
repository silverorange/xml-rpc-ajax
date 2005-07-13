function XML_RPC_Request(procedure_name, arguments)
{
	this.procedure_name = procedure_name;
	this.arguments = arguments;
}

XML_RPC_Request.prototype.toString = function()
{
	var data = '<' + '?xml version="1.0" encoding="UTF-8"?' + '>\n' + 
		'<methodCall>\n' +
		'<methodName>' + this.procedure_name + '</methodName>\n' +
		'<params>\n<param><value>';

	// for now treat everything as a string
	for (var i = 0; i < this.arguments.length - 1; i++) {
		if (i < this.arguments.length - 2) {
			data = data + XHTML_Escaper.escape(this.arguments[i]) +
				'</value></param>\n<param><value>';
		} else {
			data = data + XHTML_Escaper.escape(this.arguments[i]);
		}
	}

	data = data + '</value></param>\n</params>\n' +
		'</methodCall>';

	return data;
}

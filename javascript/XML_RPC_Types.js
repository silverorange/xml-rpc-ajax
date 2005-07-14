function XML_RPC_Array(value)
{
	this.value = value;
}

XML_RPC_Array.prototype.toString = function()
{
	var value;
	var xml = '\n<array><data>\n';

	for (var i = 0; i < this.value.length; i++) {
		value = XML_RPC_Request.getNewValue(this.value[i]);
		
		xml = xml + '<value>' + value.toString() + '</value>\n';
	}

	xml = xml + '</data></array>\n';

	return xml;
}

function XML_RPC_String(value)
{
	this.value = value;
}

XML_RPC_String.prototype.toString = function()
{
	var xml = '<string>' + XHTML_Escaper.escape(this.value) +
		'</string>';

	return xml;
}

function XML_RPC_Double(value)
{
	switch (value) {
	case Infinity:
	case NaN:
	case Number.NaN:
	case Number.POSITIVE_INFINITY:
	case Number.NEGATIVE_INFINITY:
		this.value = 0;
		break;
	default:
		this.value = value;
		break;
	}
}

XML_RPC_Double.prototype.toString = function()
{
	var xml = '<double>' + this.value + '</double>';

	return xml;
}

function XML_RPC_Boolean(value)
{
	this.value = value;
}

XML_RPC_Boolean.prototype.toString = function()
{
	var xml = '<boolean>' + this.value + '</boolean>';

	return xml;
}

function XML_RPC_Struct(value)
{
	this.value = value;
}

XML_RPC_Struct.prototype.toString = function()
{
	var value;
	var xml = '<struct>\n';
	for (var member in this.value) {
		value = XML_RPC_Request.getNewValue(this.value[member]);

		xml = xml + '<member>\n<name>' + member + '</name>\n' +
			'<value>' + value.toString() + '</value>\n</member>\n';
	}
	xml = xml + '</struct>\n';

	return xml;
}

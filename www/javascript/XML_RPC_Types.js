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
XML_RPC_Array.prototype.toXmlRpc = function()
{
	var value;
	var xml = '\n<array><data>\n';

	for (var i = 0; i < this.value.length; i++) {
		value = XML_RPC_Request.getNewValue(this.value[i]);
		
		xml = xml + '<value>' + value.toXmlRpc() + '</value>\n';
	}

	xml = xml + '</data></array>\n';

	return xml;
}

/**
 * A string XML-RPC type
 *
 * @param string value the string this XML-RPC type represents.
 */
function XML_RPC_Array(value)
{
	this.value = value;
}
function XML_RPC_String(value)
{
	this.value = value;
}

/**
 * Returns this XML-RPC type as a well formed XML fragment
 *
 * @return string this XML-RPC type as a well formed XML fragment.
 */
XML_RPC_String.prototype.toXmlRpc = function()
{
	var xml = '<string>' + XHTML_Escaper.escape(this.value) +
		'</string>';

	return xml;
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
		this.value = 0;
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
XML_RPC_Double.prototype.toXmlRpc = function()
{
	var xml = '<double>' + this.value + '</double>';

	return xml;
}

/**
 * A boolean XML-RPC type
 *
 * @param boolean value the booean this XML-RPC type represents.
 */
function XML_RPC_Boolean(value)
{
	this.value = value;
}

/**
 * Returns this XML-RPC type as a well formed XML fragment
 *
 * @return string this XML-RPC type as a well formed XML fragment.
 */
XML_RPC_Boolean.prototype.toXmlRpc = function()
{
	var xml = '<boolean>' + this.value + '</boolean>';

	return xml;
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
XML_RPC_Struct.prototype.toXmlRpc = function()
{
	var value;
	var xml = '<struct>\n';
	for (var member in this.value) {
		value = XML_RPC_Request.getNewValue(this.value[member]);

		xml = xml + '<member>\n<name>' + member + '</name>\n' +
			'<value>' + value.toXmlRpc() + '</value>\n</member>\n';
	}
	xml = xml + '</struct>\n';

	return xml;
}

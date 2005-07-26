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
		value = XML_RPC_Request.getNewValue(this.value[i]);
		
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
	this.value = value;
}

/**
 * Returns this XML-RPC type as a well formed XML fragment
 *
 * @return string this XML-RPC type as a well formed XML fragment.
 */
XML_RPC_String.prototype.marshall = function()
{
	var value = this.value.replace('&', '&amp;').replace('<', '&lt;');
	var xml = '<string>' + value + '</string>';

	return xml;
}

XML_RPC_String.unmarshall = function(string_node)
{
	var string_value = string_node.firstChild.nodeValue;
	string_value = string_value.replace('&lt;', '<').replace('&amp;', '&');

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
	var double_value = parseDouble(value);
	return double_value;
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
XML_RPC_Boolean.prototype.marshall = function()
{
	var xml = '<boolean>' + this.value + '</boolean>';

	return xml;
}

XML_RPC_Boolean.unmarshall = function(boolean_node)
{
	var value = boolean_node.firstChild.nodeValue;
	var boolean_value = (value) ? true : false;
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
		value = XML_RPC_Request.getNewValue(this.value[member]);

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

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

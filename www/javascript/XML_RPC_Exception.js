function XML_RPC_Exception(number, message)
{
	this.number = number;
	this.message = message;
}

XML_RPC_Exception.prototype = new Error;

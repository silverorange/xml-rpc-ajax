<?php

/**
 * The initial setup of an XML-RPC Ajax component
 *
 * @package   xml-rpc-ajax
 * @copyright 2005 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class XML_RPCAjax
{
	public function display()
	{
		?>
		<script src="xml-rpc-ajax/javascript/XML_RPC_Types.js" type="text/javascript"></script>
		<script src="xml-rpc-ajax/javascript/XML_RPC_Exception.js" type="text/javascript"></script>
		<script src="xml-rpc-ajax/javascript/XML_RPC_Request.js" type="text/javascript"></script>
		<script src="xml-rpc-ajax/javascript/XML_RPC_Response.js" type="text/javascript"></script>
		<script src="xml-rpc-ajax/javascript/XML_RPC_Client.js" type="text/javascript"></script>
		<?php
	}
}

?>

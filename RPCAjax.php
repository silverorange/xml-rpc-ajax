<?php

require_once 'Swat/SwatJavaScriptHtmlHeadEntry.php';

/**
 * The initial setup of an XML-RPC Ajax component
 *
 * @package   xml-rpc-ajax
 * @copyright 2005-2006 silverorange
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

	/**
	 * Gets the HTML head entries required by this AJAX XML-RPC client
	 *
	 * @return array a reference to an array of SwatHtmlHeadEntry objects
	 *                containing the required JavaScript files for this AJAX
	 *                XML-RPC client.
	 */
	public static function &getHtmlHeadEntries()
	{
		$entries = array();
		$entries['xml-rpc-ajax/javascript/XML_RPC_Types.js'] =
			new SwatJavaScriptHtmlHeadEntry(
				'xml-rpc-ajax/javascript/XML_RPC_Types.js');

		$entries['xml-rpc-ajax/javascript/XML_RPC_Exception.js'] =
			new SwatJavaScriptHtmlHeadEntry(
				'xml-rpc-ajax/javascript/XML_RPC_Exception.js');

		$entries['xml-rpc-ajax/javascript/XML_RPC_Request.js'] =
			new SwatJavaScriptHtmlHeadEntry(
				'xml-rpc-ajax/javascript/XML_RPC_Request.js');

		$entries['xml-rpc-ajax/javascript/XML_RPC_Response.js'] =
			new SwatJavaScriptHtmlHeadEntry(
				'xml-rpc-ajax/javascript/XML_RPC_Response.js');

		$entries['xml-rpc-ajax/javascript/XML_RPC_Client.js'] =
			new SwatJavaScriptHtmlHeadEntry(
				'xml-rpc-ajax/javascript/XML_RPC_Client.js');

		return $entries;
	}
}

?>

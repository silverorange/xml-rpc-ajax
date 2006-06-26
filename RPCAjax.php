<?php

require_once 'Swat/SwatHtmlHeadEntrySet.php';
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
	 * @return SwatHtmlHeadEntrySet a set of SwatHtmlHeadEntry objects
	 *                               containing the required JavaScript files
	 *                               for an AJAX XML-RPC client.
	 */
	public static function getHtmlHeadEntrySet()
	{
		$set = new SwatHtmlHeadEntrySet();

		$set->addEntry(new SwatJavaScriptHtmlHeadEntry(
				'packages/xml-rpc-ajax/javascript/XML_RPC_Types.js'));

		$set->addEntry(new SwatJavaScriptHtmlHeadEntry(
				'packages/xml-rpc-ajax/javascript/XML_RPC_Exception.js'));

		$set->addEntry(new SwatJavaScriptHtmlHeadEntry(
				'packages/xml-rpc-ajax/javascript/XML_RPC_Request.js'));

		$set->addEntry(new SwatJavaScriptHtmlHeadEntry(
				'packages/xml-rpc-ajax/javascript/XML_RPC_Response.js'));

		$set->addEntry(new SwatJavaScriptHtmlHeadEntry(
				'packages/xml-rpc-ajax/javascript/XML_RPC_Client.js'));

		return $set;
	}
}

?>

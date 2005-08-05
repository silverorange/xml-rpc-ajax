<?php
require_once 'XML/RPC2/Server.php';

// For testing 
/*
$GLOBALS['HTTP_RAW_POST_DATA'] = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<methodCall>
<methodName>add_line</methodName>
<params>
<param><value><string>Mike: Says Hi!</string></value></param>
</params>
</methodCall>
XML;
*/

// Set content type to XML
header('Content-type: text/xml');

// Disable any caching with HTTP headers
// Any date in the past will do here
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

// Set always modified
// for HTTP/1.1
header('Cache-Control: no-cache, must-revalidate max-age=0');
// for HTTP/1.0
header('Pragma: no-cache');

/**
 * A demo XML-RPC server
 *
 * This server serves XML-RPC requests from the xml-rpc-ajax demo.
 *
 * @package   xml-rpc-ajax
 * @copyright 2005 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class MyServer
{
	/**
	 * Multiplies two numbers
	 *
	 * @param int $x the left side.
	 * @param int $y the right side.
	 *
	 * @return string the product.
	 */
	public static function multiply($x, $y)
	{
		return number_format($x * $y, 2);
	}

	/**
	 * Gets a semi-unique colour based on an ip address
	 *
	 * @param string $ip the ip address to get a colour for.
	 *
	 * @return string the hex colour based on the given ip address.
	 *
	 * @xmlrpc.hidden
	 */
	public static function colorify_ip($ip)
	{
		$parts = explode('.', $ip);
		$color = sprintf('#%02s%02s%02s',
				dechex($parts[1]), dechex($parts[2]), dechex($parts[3]));

		return $color;
	}

	/**
	 * Adds a line to the graffiti wall.
	 *
	 * @param string $message the message to add to the wall.
	 */
	public static function add_line($message)
	{
		$f = fopen('/tmp/wall.html', 'a');
		$dt = date('Y-m-d h:i:s');
		$message = strip_tags(stripslashes($message));
		$remote = $_SERVER['REMOTE_ADDR'];

		// generate unique-ish color for IP
		$color = self::colorify_ip($remote);

		fwrite($f, "<span style=\"color:{$color}\">{$dt}</span> " .
			"{$message}<br />\n");

		fclose($f);

		// need to return something here.
		return true;
	}

	/**
	 * Refreshes the graffiti wall
	 *
	 * @return string the updated contents of the wall.
	 */
	public static function refresh()
	{
		$lines = file('/tmp/wall.html');

		// return the last 25 lines
		$contents = implode("\n", array_slice($lines, -25));

		return $contents;
	}

	/**
	 * Searches for a country based on name
	 *
	 * @param string $keyword the country name to look for.
	 *
	 * @return array an array of matching country names.
	 */
	public static function search($keyword)
	{
		$keyword = strtolower($keyword);
		
		include_once 'countries.php';

		$return_array = array();
		
		if (strlen($keyword) > 0) {
			foreach($countries as $country) {
				if (strpos(strtolower($country), $keyword) !== false) {
					$return_array[] = $country;
				}
			}
		} else {
			$return_array[] = '[none]';
		}

		if (count($return_array) == 0) {
			$return_array[] = '[none]';
		}

		return $return_array;
	}
}

$server = XML_RPC2_Server::create('MyServer');
$server->handleCall();
?>

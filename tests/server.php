<?php

/**
 * @package   xml-rpc-ajax
 * @copyright 2005-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */

require_once __DIR__ . '/vendor/autoload.php';

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
 * @package   XML_RPCAjax
 * @copyright 2005-2016 silverorange
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
     * Searches for a country based on name
     *
     * @param string $keyword the country name to look for.
     *
     * @return array an array of matching country names.
     */
    public static function search($keyword)
    {
        $keyword = strtolower($keyword);

        include_once __DIR__ . '/countries.php';

        $return_array = [];

        if ($keyword != '') {
            foreach ($countries as $country) {
                if (str_contains(strtolower($country), $keyword)) {
                    $return_array[] = $country;
                }
            }
        } else {
            $return_array[] = '[none]';
        }

        if (count($return_array) === 0) {
            $return_array[] = '[none]';
        }

        return $return_array;
    }
}

$server = XML_RPC2_Server::create('MyServer');
$server->handleCall();

?>

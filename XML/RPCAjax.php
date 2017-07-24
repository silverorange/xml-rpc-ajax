<?php

/**
 * The initial setup of an XML-RPC Ajax component
 *
 * @package   XML_RPCAjax
 * @copyright 2005-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class XML_RPCAjax
{
    /**
     * The package identifier
     */
    const PACKAGE_ID = 'XML_RPCAjax';

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

        $yui = new SwatYUI(array('connection'));
        $set->addEntrySet($yui->getHtmlHeadEntrySet());
        $set->addEntry(
            new SwatJavaScriptHtmlHeadEntry(
                'packages/xml-rpc-ajax/javascript/xml-rpc-ajax.js',
                self::PACKAGE_ID
            )
        );

        return $set;
    }
}

?>

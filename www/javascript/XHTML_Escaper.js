/**
 * Static XHTML filtering methods
 */
function XHTML_Escaper()
{
}

/**
 * Escapes a string so that it may be included in an XML document
 *
 * @param string text the text to escape.
 *
 * @return string the escaped text.
 */
XHTML_Escaper.escape = function(text)
{
	text = text.replace('&', '&amp;');
	text = text.replace('<', '&lt;');
	text = text.replace('>', '&gt;');
	text = text.replace('"', '&quot;');
	return text;
}

/**
 * Unescapes a an escaped string so that it is returned to its original state
 *
 * @param string text the text to unescape.
 *
 * @return string the unescaped text.
 */
XHTML_Escaper.unescape = function(text)
{
	text = text.replace('&quot;', '"');
	text = text.replace('&gt;',   '>');
	text = text.replace('&lt;',   '<');
	text = text.replace('&amp;',  '&');
	return text;
}

function XHTML_Escaper()
{
}

XHTML_Escaper.escape = function(text)
{
	text = text.replace('&', '&amp;');
	text = text.replace('<', '&lt;');
	text = text.replace('>', '&gt;');
	text = text.replace('"', '&quot;');
	return text;
}

XHTML_Escaper.unescape = function(text)
{
	text = text.replace('&quot;', '"');
	text = text.replace('&gt;',   '>');
	text = text.replace('&lt;',   '<');
	text = text.replace('&amp;',  '&');
	return text;
}

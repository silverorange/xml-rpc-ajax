<?php header('Content-type: text/html; charset=UTF-8'); ?>
<html>
	<head>
		<title>Multiplier</title>

		<script src="../www/javascript/XML_RPC_Types.js" type="text/javascript"></script>
		<script src="../www/javascript/XML_RPC_Exception.js" type="text/javascript"></script>
		<script src="../www/javascript/XML_RPC_Request.js" type="text/javascript"></script>
		<script src="../www/javascript/XML_RPC_Response.js" type="text/javascript"></script>
		<script src="../www/javascript/XML_RPC_Client.js" type="text/javascript"></script>
		<script src="../www/javascript/XHTML_Escaper.js" type="text/javascript"></script>
		<script>
		var client = new XML_RPC_Client('server.php');

		function do_multiply_cb(z)
		{
			document.getElementById('z').innerHTML = z;
		}

		function do_search_cb(countries)
		{
			var results = document.getElementById('results');
			var html;
			
			if (countries[0] == '[none]') {
				html = '';
			} else {
				html = '<ul>';
				for (var i = 0; i < countries.length; i++) {
					html = html + '<li onclick="click_result(this);">' +
						countries[i] + '</li>';
				}
				html = html + '</ul>';
			}

			results.innerHTML = html;
		}
		
		function do_multiply()
		{
			var x = document.getElementById('x').value;
			var y = document.getElementById('y').value;

			client.callXmlRpcProcedure('multiply', [x, y], do_multiply_cb);
		}

		function do_search()
		{
			var country = document.getElementById('country').value;

			client.callXmlRpcProcedure('search', [country], do_search_cb);
		}

		function click_result(list_element)
		{
			var country = document.getElementById('country');
			var results = document.getElementById('results');
			
			country.value = list_element.innerHTML;
			country.select();
			results.innerHTML = '';
		}
		
		function update_page()
		{
			do_multiply();
			do_search();
		}
		</script>
		<style>
		body {
			font-family: sans-serif;
		}
		h1 {
			margin-top: 10px;
		}
		#z {
			background: #f0f0f0;
			color: #808080;
			border-top: 1px solid #808080;
			border-left: 1px solid #808080;
			border-bottom: 1px solid #d0d0d0;
			border-right: 1px solid #d0d0d0;
			padding: 2px;
		}
		input {
			border-top: 1px solid #808080;
			border-left: 1px solid #808080;
			border-bottom: 1px solid #d0d0d0;
			border-right: 1px solid #d0d0d0;
			padding: 2px;
			font-size: 100%;
		}
		ul {
			position: relative;
			top: -1px;
			border: 1px solid #e0e0e0;
			width: 200px;
			list-style: none;
			padding: 0px;
			margin-top: 0px;
			font-size: 80%;
		}
		div.demo {
			padding-bottom: 15px;
			border-bottom: 1px solid #e0e0e0;
		}
		li {
			padding: 3px 5px 3px 10px;
		}
		li:hover {
			background: #c0d0f0;
			cursor: pointer;
		}
		</style>
	</head>
	<body onload="update_page();">
		<div class="demo">
		<h1>Live Multiply Demo</h1>
		<p>This demo multiplies two numbers using an XML-RPC server without
		reloading the page. The result box is updated using client-side
		javascript.</p>
		<input type="text" id="x" value="2" size="3" onkeyup="do_multiply();" onfocus="this.select();" /> ×
		<input type="text" id="y" value="3" size="3" onkeyup="do_multiply();" onfocus="this.select();" /> =
		<span id="z">6</span>
		</div>
		<div class="demo">
		<h1>Live Search Demo</h1>
		<p>This demo searches for countries on an XML-RPC server as
		as you type the country name.</p>
		<input type="text" id="country" value="" size="15" onkeyup="do_search();" onfocus="this.select();" />
		<div id="results">
		</div>
		</div>
	</body>
</html>

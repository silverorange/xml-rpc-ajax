Asynchronous XML-RPC Client for JavaScript
==========================================
This package allows you to make XML-RPC requests and receive XML-RPC responses
asynchronously using JavaScript. The asynchronous calls are handled by the
JavaScript `XMLHttpRequest` object that allows XML-RPC calls to be made without
causing a full page request.

Without a lot of preamble here's why this is cool: You can make server calls
at any time from client javascript. This enables many of the *live* features
available in newer web applications. Things like live-search and instant-save
become feasable. Complex processing can be deferred to the server and complex
procedutes can even be written in a more suitable language than JavaScript. The
results are then sent to the client without having to reload the page. Further
still, it is possible to make database calls on the server from client-side
JavaScript.

This XML-RPC client library is derived from the version 0.10 of the Sajax PHP
library written by ModernMethod Inc and licensed under a BSD style license.
See http://modernmethod.com/sajax/ for more details. This package itself is
released under the LGPL.

Remore procedure calls from the XML-RPC client occur as follows:

 - Web-server renders JavaScript on client browser.
 - Web broswser parses JavaScript as it renders the page.
 - Client JavaScript calls a remote procedure using the
   `XML_RPC_Client::callRemoteProcedure()` method.
 - `XML_RPC_Client` object builds an XML-RPC request
 - `XML_RPC_Client` object sends the XML-RPC request via an `XMLHttpRequest`
   object to the XML-RPC server.
 - XML-RPC server behaves as always. It parses the request and sends the client
   an XML-RPC response.
 - Client JavaScript finishes receiving XML-RPC server response and parses
   the XML response into an appropriate JavaScript object.
 - The JavaScript object is passed to a JavaScript callback function.

The client-side XML-RPC response parser creates an appropriately typed
JavaScript object automatically. This means arrays are returned as `Array`
objects and structs are returned as simple `Object`s.

There are several JavaScript objects being used:

XML_RPC_Client
--------------
Makes asynchronous XML-RPC client requests

XML_RPC_Response
----------------
Parses and unmarshells XML-RPC responses

XML_RPC_Request
---------------
Creates XML-RPC requests

XML_RPC_Exception
-----------------
Thrown in various situations by this package

XML_RPC_*type*
--------------
Represents an XML-RPC data type and can marshall/unmarshall an javascript
type.

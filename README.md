# Asynchronous XML-RPC Client for JavaScript

This package allows you to make XML-RPC requests and receive XML-RPC responses
asynchronously using JavaScript. The asynchronous calls are handled by the
JavaScript `XMLHttpRequest` object that allows XML-RPC calls to be made without
causing a full page request.

This XML-RPC client library is derived from the version 0.10 of the Sajax PHP
library written by ModernMethod, Inc. and licensed under a BSD style license.
See http://modernmethod.com/sajax/ for more details. This package itself is
released under the LGPL.

Remote procedure calls from the XML-RPC client occur as follows:

```js
var client = new XML_RPC_Client('https://www.myhost.com/my/endpoint');
client.callProcedure(
  'myMethodName',
  function (response) {
    // parsed XML-RPC response
    console.log(response);
  },
  [arg1, arg2, arg3],
  [type1, type2, type3]
);
```

The second parameter to `callProcedure()` is a callback that is fired when the
XML-RPC response is received and parsed.

Types are passed as an array of strings. If types are not passed, type
inference is used. JavaScript types do not map directly to XML-RPC types so
this may not always work perfectly.

The client-side XML-RPC response parser creates an appropriately typed
JavaScript object automatically. This means arrays are returned as `Array`
values and structs are returned as `Object` hash-maps.

There are several JavaScript objects being used:

## XML_RPC_Client

Makes asynchronous XML-RPC client requests

## XML_RPC_Response

Parses and unmarshells XML-RPC responses

## XML_RPC_Request

Creates XML-RPC requests

## XML_RPC_Exception

Thrown in various situations by this package

## Types

Each XML-RPC data type has a corresponding JavaScript object to handle
marshalling and unmarshalling data.

- XML_RPC_Date
- XML_RPC_Array
- XML_RPC_String,
- XML_RPC_Double
- XML_RPC_Int
- XML_RPC_Boolean
- XML_RPC_Struct

## Installation

Make sure the silverorange composer repository is added to the `composer.json`
for the project and then run:

```sh
composer require silverorange/xml-rpc-ajax
```

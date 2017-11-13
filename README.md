This library helps with creating and executing multiple requests simultaneously using the Multi-cUrl functions of PHP.

### Requirements

* PHP 5.3 or newer
* cUrl must be installed

### Usage

The main class of the library is the `Manager`, to which you can add as many requests as you like and which will execute
them. For each request to be started, create an instance of the `Entity\Request` class and set its properties as
required, and add it to the manager using `$manager->addRequest($request)`.

To any moment, you may want to call `$manager->execute()` of the manager. This will start any not yet started requests,
and will check all already running requests if any of them has finished. This method will return immediately, and will
not wait for the requests to be finished. If you want to wait for all requests to be finished, call
`$manager->waitForRequests()`.

Once a request is finished, use `$request->getResponse()` to get the information of the response. You may want to check
`$response->getErrorCode()` and `$response->getErrorMessage()` to get any information in case the request has failed.
It is possible to add a callback to the request using `$request->setOnCompleteCallback($callback)`. This method will be
executed as soon as the manager recognizes the request to be finished.

### Example

Here is a full example demonstrating the use of the manager:

```php
<?php

use BluePsyduck\MultiCurl\Manager;
use BluePsyduck\MultiCurl\Entity\Request;

$manager = new Manager();

$requestFoo = new Request();
$requestFoo->setUrl('http://localhost/data.php?action=foo');
$manager->addRequest($foo)
        ->execute(); // Already start the first request.

$requestBar = new Request();
$requestBar->setUrl('http://localhost/data.php?action=bar');

$manager->addRequest($requestBar)
        ->execute(); // Start the second request. First request will be checked if finished.

// Some other code.

$manager->waitForAllRequests(); // Will wait for both requests to be finished.

// Do something with the responses
var_dump($requestFoo->getResponse());
var_dump($requestBar->getResponse());
```

### Notes

* The manager will not limit the number of currently running requests, so make sure to not add too many requests at
  once.

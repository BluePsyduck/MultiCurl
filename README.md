This library helps with creating and executing multiple requests simultaneously using the Multi-cUrl functions of PHP.

### Requirements

* PHP 5.3 or newer
* cUrl must be installed

### Usage

The main class of the lib is the `Manager`, to which you can add as many requests as you like and which will execute 
them. For each request to be started, create an instance of the `Entity\Request` class and set its properties as 
required, and add it to the manager using `->addRequest($name, $request)`. The name you specify for the request is used 
later for retrieving the response.

Once all requests are added, call `->execute()` on the manager once. You may now do something else to span the delay 
the requests may have, or you may directly call `->waitForRequests()` to delay script execution as long as not all
requests have been finished.

At the end, get the responses of the requests by calling `->getResponse($name)` and specifying the name of the request.
Before accessing any of the responses, make sure `->waitForRequests()` has been called, as otherwise the response may
not be available yet.

### Example

Here is a full example demonstrating the use of the manager:

```php
<?php

use BluePsyduck\MultiCurl\Manager;
use BluePsyduck\MultiCurl\Entity\Request;

$requestFoo = new Request();
$requestFoo->setUrl('http://localhost/data.php?action=foo');

$requestBar = new Request();
$requestBar->setUrl('http://localhost/data.php?action=bar');

$manager = new Manager();
$manager->addRequest('foo', $requestFoo)
        ->addRequest('bar', $requestBar);

$manager->execute()
        ->waitForRequests();

$responseFoo = $manager->getResponse('foo');
$responseBar = $manager->getResponse('bar');
```

### Notes

* As all requests are executed simultaneously, pay attention to how many requests you add to the manager. 
* When creating a new request, use a new instance of the manager and do not re-use an existing one, as this may lead
  to unwanted results.
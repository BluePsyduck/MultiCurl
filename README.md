# BluePsyduck's MultiCurl Library

[![Latest Stable Version](https://poser.pugx.org/bluepsyduck/multicurl/v/stable)](https://packagist.org/packages/bluepsyduck/multicurl) [![Total Downloads](https://poser.pugx.org/bluepsyduck/multicurl/downloads)](https://packagist.org/packages/bluepsyduck/multicurl) [![License](https://poser.pugx.org/bluepsyduck/multicurl/license)](https://packagist.org/packages/bluepsyduck/multicurl) [![Build Status](https://travis-ci.org/BluePsyduck/MultiCurl.svg?branch=master)](https://travis-ci.org/BluePsyduck/MultiCurl) [![codecov](https://codecov.io/gh/BluePsyduck/MultiCurl/branch/master/graph/badge.svg)](https://codecov.io/gh/BluePsyduck/MultiCurl)

This library helps with creating and executing multiple requests simultaneously using the Multi-cURL functions of PHP.

## Requirements

* PHP 7.0 or newer
* PHP cURL extension

## Usage

The main class of the library is the `MultiCurlManager`, to which you can add as many requests as you like and which 
will execute them. For each request to be started, create an instance of the `Entity\Request` class and set its 
properties as required, and add it to the manager using `$manager->addRequest($request)`. Added requests will 
immediately be executed, without blocking the script execution.

To wait for a certain request to finish, call `$manager->waitForSingleRequest($request)`. To wait for all requests to 
finish, call `$manager->waitForAllRequests()`. These methods will block script execution until the desired requests are
finished.

Once a request is finished, use `$request->getResponse()` to get the information of the response. You may want to check
`$response->getErrorCode()` and `$response->getErrorMessage()` to get any information in case the request has failed.

The requests offer two callbacks:
- onInitialize: This callback is triggered once the underlying cURL request has been initialized. Use this callback to
  further manipulate the cURL. After this callback, the cURL request gets executed.
- onComplete: This callback is triggered once the cURL request finished and the response has been parsed into the 
  entity.

## Examples

Here is a basic example demonstrating the use of the `MultiCurlManager`:

```php
<?php

use BluePsyduck\MultiCurl\MultiCurlManager;
use BluePsyduck\MultiCurl\Entity\Request;

$manager = new MultiCurlManager();

$requestFoo = new Request();
$requestFoo->setUrl('http://localhost/data.php?action=foo');
$manager->addRequest($requestFoo); // Will execute the first request, but will not wait for it to finish.

$requestBar = new Request();
$requestBar->setUrl('http://localhost/data.php?action=bar');

$manager->addRequest($requestBar); // Will execute the second request, having both run parallel.

// Some other code.

$manager->waitForAllRequests(); // Will wait for both requests to be finished.

// Do something with the responses
var_dump($requestFoo->getResponse());
var_dump($requestBar->getResponse());
```

Here is another example demonstrating limiting the number of parallel requests:

```php
<?php 

use BluePsyduck\MultiCurl\MultiCurlManager;
use BluePsyduck\MultiCurl\Entity\Request;

$manager = new MultiCurlManager();
$manager->setNumberOfParallelRequests(4); // Limit number of parallel requests.

for ($i = 0; $i < 16; ++$i) {
    $request = new Request();
    $request->setUrl('http://localhost/data.php?i=' . $i);
    $request->setOnInitializeCallback(function(Request $request) use ($i) {
        echo 'Initialize #' . $i . PHP_EOL;
    });
    $request->setOnCompleteCallback(function(Request $request) use ($i) {
        echo 'Complete #' . $i . PHP_EOL;
    });
    $manager->addRequest($request);
}

// Now 16 requests have been scheduled to be executed, but only 4 requests will run in parallel.
// Once a request finishes, the next one will be executed.

$importantRequest = new Request();
$importantRequest->setUrl('http://localhost/data.php?type=important');
$importantRequest->setOnInitializeCallback(function(Request $request) use ($i) {
    echo 'Initialize important request' . PHP_EOL;
});
$importantRequest->setOnCompleteCallback(function(Request $request) use ($i) {
    echo 'Complete important request' . PHP_EOL;
});
$manager->addRequest($importantRequest); // Important request will not be executed because of the limit.

$manager->waitForSingleRequest($importantRequest); // This will execute the request, ignoring the limit.
// So now actually 5 requests are running in parallel.

echo 'Important request has finished.' . PHP_EOL;

$manager->waitForAllRequests(); // Wait for all the other requests.
```

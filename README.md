To be continued...

### Example

```php
<?php

use BluePsyduck\MultiCurl\Manager;
use BluePsyduck\MultiCurl\Entity\Request;

$request1 = new Request();
$request1->setUrl('http://www.example.com/');

$request2 = new Request();
$request2->setUrl('http://www.example.org/');

$manager = new Manager();
$manager->addRequest('com', $request1)
        ->addRequest('org', $request2);

$manager->execute()
        ->waitForRequests();

var_dump($manager->getResponse('com'));
var_dump($manager->getResponse('org'));
```

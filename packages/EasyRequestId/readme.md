---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

Microservices infrastructures are common, one request can involve N different applications sending requests to each other,
and it can be hard to link events occurring across them. This package objective is to create a standard way in PHP projects
to resolve/share IDs across projects so linking requests becomes easier!

It is based on 2 different IDs:

- **request_id:** ID of request specific to each project
- **correlation_id:** shared ID across projects for the same initial request

On the top of resolving those IDs for you, this package also comes with bridges to different packages to automatically
include those IDs in your:

- **bugsnag notifications:** using [EasyBugsnag][4]
- **error responses:** using [EasyErrorHandler][5]
- **logs:** using [EasyLogging][6]
- **webhooks:** using [EasyWebhook][7]

<br>

### Dependencies

This package has dependencies on the following packages, please see their documentation directly:

- [EasyRandom][1]

<br>

### Require package (Composer)

The recommended way to install this package is to use [Composer][3]:

```bash
$ composer require eonx-com/easy-request-id
```

### Usage

This package is based on a single service providing the requestId and correlationId anywhere you need them:

```php
// src/Controller/MyController.php

namespace App\Controller;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;

final class MyController
{
    public function __construct(private RequestIdProviderInterface $requestIdProvider)
    {
    }

    public function __invoke()
    {
        $requestId = $this->requestIdProvider->getRequestId();
        $correlationId = $this->requestIdProvider->getCorrelationId();

        // Use the IDs in your logic...
    }
}
```

[1]: https://github.com/eonx-com/easy-random

[3]: https://getcomposer.org/

[4]: https://github.com/eonx-com/easy-bugsnag

[5]: https://github.com/eonx-com/easy-error-handler

[6]: https://github.com/eonx-com/easy-error-logging

[7]: https://github.com/eonx-com/easy-webhook

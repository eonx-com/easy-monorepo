---eonx_docs---
title: 'ApiPlatform: ApiResource No Request Payload'
weight: 1002
---eonx_docs---

By default [ApiPlatform][1], requires an ApiResource to have at least one property as it uses them to:

- generate the documentation
- serialise/de-serialise the ApiResource from the request and to the response

<br>

This can be an issue in the case you want to create a simple endpoint with no request payload.
This package provides a feature to allow you to create an ApiResource with no request payload.

<br>

### No Properties ApiResource

To create a simple API endpoint with no request payload but still beneficiate of the auto generated documentation and
ApiPlatform features such as DataPersister, create an ApiResource that implements the `EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces`.

```php
// src/Api/Resource/EmailsSendResource.php

namespace App\Api\Resource;

use ApiPlatform\Core\Annotation\ApiResource;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\NoPropertiesApiResourceInterface;

/**
 * @ApiResource()
 */
final class EmailsSendResource implements NoPropertiesApiResourceInterface
{
    // No properties!
}
```

[1]: https://api-platform.com/

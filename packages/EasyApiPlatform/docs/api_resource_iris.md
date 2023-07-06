---eonx_docs---
title: 'ApiPlatform: ApiResource IRIs'
weight: 1001
---eonx_docs---

By default [ApiPlatform][1] requires an ApiResource to have at least:

- A unique identifier: Used as `id` route parameter to generate the IRI for the ApiResource
- A GET item operation: The Symfony Router is used to generate the IRI for the ApiResource and it requires a route

This can be an issue in the case you want to create a custom API endpoint, not part of standard CRUD, to trigger an
action of your choice.

<br>

This package provides different simple solutions to allow you to create any custom endpoint your business logic requires:

- No IRI Item: Allows you to create an ApiResource with no IRI support
- Self Provided IRI: Allows you to create an ApiResource that provide the IRI itself

<br>

### No IRI Item

Did you ever try to create a custom API endpoint outside of the traditional CRUD using ApiPlatform?
An API endpoint that doesn't return a traditional resource with an identifier?
How easy was it? ...

<br>

Alright let's take a simple example, you want to implement an API endpoint as `POST - /api/emails/send` and this will
return in the best case scenario something like:

```json
{
    "message": "X emails successfully sent"
}
```

<br>

As explained earlier this example will be harder than expected to implement as ApiPlatform will require you to add an
identifier on the ApiResource and an item operation that you won't ever use.

<br>

Here is the solution: `EonX\EasyApiPlatform\Routing\NoIriItemInterface`. When using the
`EasyApiPlatformSymfonyBundle` in your application, simply make your ApiResource implement this interface will allow you to get rid of
the errors and implement only what you really need.

```php
// src/Api/Resource/EmailsSendResource.php

namespace App\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use EonX\EasyApiPlatform\Routing\NoIriItemInterface;

#[ApiResource(
    operations: [new Post()],
)]
final class EmailsSendResource implements NoIriItemInterface
{
    private string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
```

<br>

### Self Provided IRI

In the previous example the created ApiResource didn't support IRI at all. This solution allows you to create ApiResources
supporting IRI generation and control the way those IRIs are generated.

Use the `EonX\EasyApiPlatform\Routing\SelfProvidedIriItemInterface`.

```php
// src/Api/Resource/EmailsSendWithIriResource.php

namespace App\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use EonX\EasyApiPlatform\Routing\SelfProvidedIriItemInterface;

#[ApiResource(
    operations: [new Post()],
)]
final class EmailsSendWithIriResource implements SelfProvidedIriItemInterface
{
    private string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getIri(): string
    {
        return '/emails/reports';
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
```

[1]: https://api-platform.com/

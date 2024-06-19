---eonx_docs---
title: Usage
weight: 2
---eonx_docs---

For the examples used in this document, we will assume we're working on a Symfony project. For any information regarding
another framework you can refer to its dedicated section. If the section doesn't exist it means that it is not supported
yet! Please feel free to contribute.

<p style="display: none">Required not to break format below</p>

<br>

### Decoding BasicAuth only

To decode BasicAuth only we will register a single decoder for BasicAuth.

```yaml
# config/packages/easy_api_token.yaml

easy_api_token:
    decoders:
        basic: null
```

<br>

Once the configuration done, we will be able to use the `EasyApiTokenDecoderFactoryInterface` to build our decoder using
its name. The name of a decoder is the key used to register it under `decoders` in the configuration, so in our case it
is `basic`.

Once the decoder built, we just have to use it to decode the ApiToken for a given request. Let's take the example of
controller.

```php
// src/Controller/MyController.php

namespace App\Controller;

use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

final class MyController
{
    public function __invoke(
        ApiTokenDecoderFactoryInterface $decoderFactory,
        Request $request
    ){
        $decoder = $decoderFactory->build('basic'); // Use the name of the decoder as an argument

        $apiToken = $decoder->decode($request); // Decode token for given request

        /**
         * $apiToken will be either a \EonX\EasyApiToken\Common\ValueObject\ApiTokenInterface instance
         * or null if no BasicAuth provided on the given request.
         */

        // Use the $apiToken...
    }
}
```

<br>

### Decoding User ApiKey only

Let's start from the previous example and see how to decode User ApiKey only instead of BasicAuth.
First, modify your configuration to add the `user-apikey` decoder.

```yaml
# config/packages/easy_api_token.yaml

easy_api_token:
    decoders:
        basic: null # Already there from previous example

        user-apikey: null
```

Once the configuration updated, simply change the argument given to the decoder factory to be `user-apikey` and... done!

```php
// src/Controller/MyController.php

namespace App\Controller;

use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface;use Symfony\Component\HttpFoundation\Request;

final class MyController
{
    public function __invoke(
        ApiTokenDecoderFactoryInterface $decoderFactory,
        Request $request
    ){
        $decoder = $decoderFactory->build('user-apikey'); // Use the name of the decoder as an argument

        $apiToken = $decoder->decode($request); // Decode token for given request

        /**
         * $apiToken will now be either a \EonX\EasyApiToken\Common\ValueObject\ApiTokenInterface instance
         * or null if no BasicAuth provided on the given request.
         */

        // Use the $apiToken...
    }
}
```

<br>

### Decoding BasicAuth and User ApiKey

We now know how to decode BasicAuth or User ApiKey, good but...
How are we supposed to know when to use one or the other?

Here comes the `chain` decoder, this decoder isn't made to handle any ApiToken strategy but to accept a list of decoders
and loop through each of them until an ApiToken is decoded! Let's see how that works.

<br>

Modify your configuration to add the `chain` decoder and set the other decoders into its `list` option.

```yaml
# config/packages/easy_api_token.yaml

easy_api_token:
    decoders:
        chain:
            list: # The list option contains a list of other decoders name
                - basic
                - user-apikey

        # Existing decoders
        basic: null
        user-apikey: null
```

<br>

Once the configuration updated, replace the argument for the decoder factory to `chain` and... Again we're done!

```php
// src/Controller/MyController.php

namespace App\Controller;

use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface;use Symfony\Component\HttpFoundation\Request;

final class MyController
{
    public function __invoke(
        ApiTokenDecoderFactoryInterface $decoderFactory,
        Request $request
    ){
        $decoder = $decoderFactory->build('chain'); // Use the name of the decoder as an argument

        $apiToken = $decoder->decode($request); // Decode token for given request

        /**
         * $apiToken now be an instance of any ApiToken returned by the configured decoders under chain.list
         * So in our case, $apiToken can be either:
         *
         * - \EonX\EasyApiToken\Common\ValueObject\BasicAuthInterface: BasicAuth provided
         * - \EonX\EasyApiToken\Common\ValueObject\ApiKeyInterface: User ApiKey provided
         * - null: neither BasicAuth or User ApiKey provided
         */

        // Use the $apiToken...
    }
}
```

<br>
<p style="display: none">Required not to break format below</p>

::: tip Congratulations
The logic illustrated within the previous examples applies to any decoders, so you now know how to handle multiple
authentication mechanism (ApiToken strategy) for any of your API endpoints and with only 3 lines of code!
:::

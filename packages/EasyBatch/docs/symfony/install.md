---eonx_docs---
title: Symfony
weight: 1000
is_section: true
section_icon: fab fa-symfony
---eonx_docs---

### Register Bundle

If you're using [Symfony Flex][1], this step has been done automatically for you. If not, you can register the bundle
yourself:

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyBatch\Bundle\EasyBatchBundle::class => ['all' => true],
];
```

### Message signing

Every batch item message is stored with an HMAC signature (`v1:<signature>:<message>` in the `message` column). The
signature is checked before the message is unserialized, so a message written to the database by someone else cannot
reach PHP's native `unserialize()`. The signature uses the `kernel.secret` parameter unless you set `signing_secret`:

```php
// config/packages/easy_batch.php

return App::config([
    'easy_batch' => [
        'signing_secret' => '%env(BATCH_SIGNING_SECRET)%',
    ],
]);
```

A message with an invalid signature throws `BatchItemMessageSignatureInvalidException`. The worker does not retry it.

`allow_unsigned_messages` controls whether a message without a signature (stored before signing existed) is still
accepted. It defaults to `true`, so batch items created before the upgrade keep working. An item is signed again every
time it is saved. Roll-out:

1. Deploy with `allow_unsigned_messages` left at `true`. New items are signed. If several applications create and
   process items of the same batch tables, set the same `signing_secret` in all of them before you deploy: by default
   each application signs with its own `kernel.secret`, and an item with a signature it cannot verify is not processed.
2. When no unsigned item is left to process (pending, failed and waiting for a retry, or waiting for approval), set
   `allow_unsigned_messages` to `false`. Only then is the native-unserialize path closed.

A future major will default `allow_unsigned_messages` to `false`.

#### Rotating the signing secret

`signing_secret` also takes a list, like Symfony's `framework.messenger.serializer.signing_secret`. The first secret
signs, all of them verify:

```php
// config/packages/easy_batch.php

return App::config([
    'easy_batch' => [
        'signing_secret' => [
            '%env(BATCH_SIGNING_SECRET)%',
            '%kernel.secret%',
        ],
    ],
]);
```

Remove the previous secret once no item signed with it is left to process. When several applications use the same
batch tables, first add the new secret as the second item in all of them, then move it to the first position.

[1]: https://symfony.com/doc/current/setup/flex.html

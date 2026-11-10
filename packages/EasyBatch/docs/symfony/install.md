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
reach PHP's native `unserialize()`. The signature uses the `kernel.secret` parameter unless you set `signing_secret`.
`allow_unsigned_messages` is required, see the roll-out below:

```php
// config/packages/easy_batch.php

return App::config([
    'easy_batch' => [
        'allow_unsigned_messages' => false,
        'signing_secret' => '%env(BATCH_SIGNING_SECRET)%',
    ],
]);
```

A message with an invalid signature throws `BatchItemMessageSignatureInvalidException`. The worker does not retry it:
the message goes to the failure transport, and the item and its batch keep their status (the item is not marked as
failed). A failed check is usually a config mistake, so fix the config (`signing_secret`, `allow_unsigned_messages`)
and run `messenger:failed:retry` to process the item.

A message without a signature (stored before 7.0) is refused too, unless `allow_unsigned_messages` is `true`. The
option has no default, so the container does not build until you choose. An unsigned item in a batch also stops
dispatching or cancelling that batch, as both read every item of the batch. An item is signed again every time it is
saved. Roll-out:

1. If batch items created before the upgrade are left to process (pending, failed and waiting for a retry, or waiting
   for approval), set `allow_unsigned_messages` to `true` before you deploy. Otherwise set it to `false` and skip
   step 2. New items are signed. If several applications create and process items of the same batch tables, set the
   same `signing_secret` in all of them before you deploy: by default each application signs with its own
   `kernel.secret`, and an item with a signature it cannot verify is not processed. 6.x cannot read signed messages:
   during a rolling deploy, 6.x workers that are still running fail on new items until a retry lands on an upgraded
   worker, and after a rollback to 6.x every signed item fails until 7.x is deployed again.
2. When the query below returns no rows, set `allow_unsigned_messages` to `false`. Only then is the native-unserialize
   path closed. Use your `batch_table` / `batch_item_table` names, and the time of the 7.0 deploy in UTC as
   `:deployedAt`:

   ```sql
   SELECT i.id
   FROM easy_batch_items i
   INNER JOIN easy_batches b ON b.id = i.batch_id
   WHERE b.status NOT IN ('succeeded', 'failed', 'cancelled')
     AND i.message IS NOT NULL
     AND (
       (i.encrypted = 0 AND i.message NOT LIKE 'v1:%')
       OR (i.encrypted = 1 AND i.updated_at < :deployedAt)
     )
   ```

   The query checks every item of an unfinished batch, as dispatching and cancelling a batch read all its items. A
   signed message starts with `v1:`. For an encrypted item the signature is inside the encrypted value, so the query
   uses `updated_at`: saving an item signs its message again and updates `updated_at`. Finished batches are not
   checked: if your application dispatches finished batches again, their unsigned items fail too.

#### Rotating the signing secret

`signing_secret` also takes a list, like Symfony's `framework.messenger.serializer.signing_secret`. The first secret
signs, all of them verify:

```php
// config/packages/easy_batch.php

return App::config([
    'easy_batch' => [
        'allow_unsigned_messages' => false,
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

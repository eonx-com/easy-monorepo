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

    EonX\EasyEncryption\Bundle\EasyEncryptionBundle::class => ['all' => true],
];
```

[1]: https://symfony.com/doc/current/setup/flex.html

### Configuration

There is no configuration required to use the EasyEncryption package with the basic encryption.
However, if you want to use the AWS CloudHSM, you need to add a specific configuration.
To connect to the CloudHSM cluster on your local machine do the following:

- Configure AWS Client VPN and connect to the Mastercard VPN:
    - Download [AWS Client VPN][4].
    - Download the VPN [config file][5].
    - Setup the profile using the config file.
    - Connect to the VPN.
- Get CloudHSM certificates from our 1Password `Mastercard PBA` vault:
    - CloudHSM Client CA: save it to `./docker/containers/api/certificates/cloudhsmca.crt`.
    - CloudHSM Client Certificate: save it to `./docker/containers/api/certificates/cloudhsmclient.crt`.
    - CloudHSM Client Private Key: save it to `./docker/containers/api/certificates/cloudhsmclient.key`.
- Rebuild containers.
- Set the following env-variables in your `./src/envs/local.env.local` file (ask your team leader for the values):
    - `ENCRYPTION_AWS_CLOUD_HSM_ENCRYPTION_KEY_NAME`
    - `ENCRYPTION_AWS_CLOUD_HSM_IP`
    - `ENCRYPTION_AWS_CLOUD_HSM_SIGN_KEY_NAME`
    - `ENCRYPTION_AWS_CLOUD_HSM_USER_PIN`

Here's an example of the configuration for the AWS CloudHSM:

```php
// config/packages/easy_encryption.php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_encryption' => [
        'default_key_name' => env('ENCRYPTION_AWS_CLOUD_HSM_ENCRYPTION_KEY_NAME'),
        'max_chunk_size' => env('ENCRYPTION_AWS_CLOUD_HSM_MAXIMUM_DATA_SIZE')->int(),
        'fully_encrypted_messages' => [
            EmailMessage::class,
            SendEmailMessage::class,
            SmsMessage::class,
        ],
        'aws_cloud_hsm_encryptor' => [
            'sdk_options' => [
                '--log-level' => 'warn',
                '--log-type' => 'term',
            ],
            'disable_key_availability_check' => true,
            'ca_cert_file' => '/var/www/var/tmp/certificates/cloudhsmca.crt',
            'ip_address' => env('ENCRYPTION_AWS_CLOUD_HSM_IP'),
            'server_client_cert_file' => '/var/www/var/tmp/certificates/cloudhsmclient.crt',
            'server_client_key_file' => '/var/www/var/tmp/certificates/cloudhsmclient.key',
            'use_aws_cloud_hsm_configure_tool' => false,
            'user_pin' => env('ENCRYPTION_AWS_CLOUD_HSM_USER_PIN'),
        ],
    ],
]);
```

### Encryptable Entity

To make an entity encryptable:

- implement the `\EonX\EasyEncryption\Encryptable\Encryptable\EncryptableInterface`
- add the `\EonX\EasyEncryption\Encryptable\Encryptable\EncryptableTrait` trait to the entity class
- add the `#[EncryptableField]` attribute to the properties you want to encrypt
- add the following properties to the entity

```php
  #[ORM\Column(type: Types::TEXT)]
  protected string $encryptedData;

  #[ORM\Column(type: Types::STRING, length: 255)]
  protected string $encryptionKeyName;
```

- Create a database migration with `php bin/console make:migration` and run it with `php bin/console doctrine:migrations:migrate` to add the new columns to the entity table.

### Symfony Messenger Integration

There are 2 ways to encrypt messages in Symfony Messenger:

- Encrypt the whole message, this will encrypt all the properties of the message. Best to use for third-party messages, where you don't have control over the message class.
- Encrypt only the required properties of the message. Best to use for your own application messages, where you have control over the message class.

First, you need to override the default the serializer in `config/packages/messenger.php`:

```php
// config/packages/messenger.php
use EonX\EasyEncryption\Encryptable\Serializer\EncryptableAwareMessengerSerializer;

...

$messengerConfig->serializer()
    ->defaultSerializer(EncryptableAwareMessengerSerializer::class);
```

Then, you can configure the messages you want to be fully encrypted for the messages in `config/packages/easy_encryption.php`:

```php
// config/packages/easy_encryption.php

$easyEncryptionConfig->fullyEncryptedMessages([
    EmailMessage::class,
    SendEmailMessage::class,
    SmsMessage::class,
]);
```

For the messages you want to encrypt only the required properties, you can add the `#[EncryptableField]` attribute to the properties you want to encrypt and implement the `\EonX\EasyEncryption\Encryptable\Encryptable\EncryptableInterface` in the
message class and
add the `\EonX\EasyEncryption\Encryptable\Encryptable\EncryptableTrait` trait to it.

```php

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableInterface;use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableTrait;

final class SomeMessage implements EncryptableInterface
{
    use EncryptableTrait;

    #[EncryptableField]
    public string $message;

    #[EncryptableField]
    public string $name;
```

Warning: encryptable message fields must not be `readonly`

#### Message signing

Every envelope produced by `EncryptableAwareMessengerSerializer` is signed with an HMAC derived from
`default_encryption_key` (`%env(APP_SECRET)%` by default). On decode the signature is verified **before** the body
is unserialized, so a forged transport payload cannot reach PHP's native `unserialize()`. All producers and
consumers sharing a transport must therefore share the same key.

The signature moves the trust boundary from "who can write to the transport" to "who holds the signing key". That
only buys you something when the two are separate secrets: give the signing key its own dedicated value via
`messenger.signing_keys` (see below) and store it so that access to it is narrower than the ability to write to the
transport. Reusing `default_encryption_key` (the default) is convenient but means anything that already exposes
that key — or any place it is shared — also lets an attacker forge a valid envelope.

`messenger.allow_unsigned_messages` controls whether an envelope without a valid signature is still accepted. It
defaults to `true` so that messages queued before the signing roll-out keep decoding during the upgrade:

```php
// config/packages/easy_encryption.php

$easyEncryptionConfig->messenger()
    ->allowUnsignedMessages(false); // set to false once every queue holds only signed messages
```

While `allow_unsigned_messages` is `true`, unsigned envelopes still reach the legacy native-unserialize path, so
the protection is only complete once it is set to `false`. Recommended roll-out:

1. Deploy with `allow_unsigned_messages` left at its default (`true`). New messages are signed; messages queued
   before the upgrade keep decoding.
2. Wait until every transport (including the failure transport) holds only signed messages — e.g. watch the queue
   drain to zero or let the oldest pre-upgrade message age out.
3. Set `allow_unsigned_messages` to `false`. Unsigned envelopes are now rejected and the native-unserialize path
   is closed.

A future major will default `allow_unsigned_messages` to `false`.

#### Rotating the signing key

`messenger.signing_keys` takes a list of keys. The first key signs every outgoing envelope; every key in the list
is tried when verifying, so a key that is being rotated out keeps accepting messages that were signed with it until
they drain. When the option is omitted it defaults to `default_encryption_key`.

```php
// config/packages/easy_encryption.php

$easyEncryptionConfig->messenger()
    ->signingKeys(['%env(MESSENGER_SIGNING_KEY)%', '%env(APP_SECRET)%']);
```

To rotate a key without losing the messages already queued:

1. Add the new key **last**: `[current, new]`. Envelopes are still signed with `current`, but `new` is already
   accepted. Deploy this to every producer and consumer.
2. Move the new key **first**: `[new, current]`. New envelopes are signed with `new`; messages still in the queue
   signed with `current` keep verifying.
3. Once every transport holds only messages signed with `new`, drop the old key: `[new]`.

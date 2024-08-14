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
- Delete the `./src/config/packages/local/easy_encryption.php` config.
- Update the `.src/config/services_local.php` config:
    - Delete the `App\Infrastructure\Encryption\Encryptor\Encryptor` service definition.
    - Delete the `App\Infrastructure\Encryption\HashCalculator\HashCalculatorInterface` service definition.

Here's an example of the configuration for the AWS CloudHSM:

```php
// config/packages/easy_encryption.php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyEncryptionConfig;

return static function (EasyEncryptionConfig $easyEncryptionConfig): void {
    $easyEncryptionConfig->defaultKeyName(env('ENCRYPTION_AWS_CLOUD_HSM_ENCRYPTION_KEY_NAME'));
    $easyEncryptionConfig->maxChunkSize(env('ENCRYPTION_AWS_CLOUD_HSM_MAXIMUM_DATA_SIZE')->int());
    $easyEncryptionConfig->fullyEncryptedMessages([
        EmailMessage::class,
        SendEmailMessage::class,
        SmsMessage::class,
    ]);
    $easyEncryptionConfig->awsCloudHsmEncryptor()
        ->enabled(true)
        ->sdkOptions([
            '--log-level' => 'warn',
            '--log-type' => 'term',
        ])
        ->disableKeyAvailabilityCheck(true)
        ->caCertFile('/var/www/var/tmp/certificates/cloudhsmca.crt')
        ->ipAddress(env('ENCRYPTION_AWS_CLOUD_HSM_IP'))
        ->serverClientCertFile('/var/www/var/tmp/certificates/cloudhsmclient.crt')
        ->serverClientKeyFile('/var/www/var/tmp/certificates/cloudhsmclient.key')
        ->useAwsCloudHsmConfigureTool(false)
        ->userPin(env('ENCRYPTION_AWS_CLOUD_HSM_USER_PIN'));
}
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

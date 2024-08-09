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

    EonX\EasyEncryption\Bridge\Symfony\EasyEncryptionSymfonyBundle::class => ['all' => true],
];
```

[1]: https://flex.symfony.com/

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

$easyEncryptionConfig->defaultKeyName(env('ENCRYPTION_AWS_CLOUD_HSM_ENCRYPTION_KEY_NAME'));
    $easyEncryptionConfig->maxChunkSize(env('ENCRYPTION_AWS_CLOUD_HSM_MAXIMUM_DATA_SIZE')->int());
    $easyEncryptionConfig->fullyEncryptedMessages([
        EmailMessage::class,
        SendEmailMessage::class,
        SmsMessage::class,
    ]);
    $easyEncryptionConfig->awsPkcs11Encryptor()
        ->enabled(true)
        ->awsCloudHsmSdkOptions([
            '--log-level' => 'warn',
            '--log-type' => 'term',
        ])
        ->disableKeyAvailabilityCheck(true)
        ->hsmCaCert('/var/www/var/tmp/certificates/cloudhsmca.crt')
        ->hsmIpAddress(env('ENCRYPTION_AWS_CLOUD_HSM_IP'))
        ->serverClientCertFile('/var/www/var/tmp/certificates/cloudhsmclient.crt')
        ->serverClientKeyFile('/var/www/var/tmp/certificates/cloudhsmclient.key')
        ->useAwsCloudHsmConfigureTool(false)
        ->userPin(env('ENCRYPTION_AWS_CLOUD_HSM_USER_PIN'));
```

### Encryptable Entity

To make an entity encryptable:
- implement the `\EonX\EasyEncryption\Interfaces\EncryptableInterface`
- add the `\EonX\EasyEncryption\Traits\EncryptableTrait` trait to the entity class
- add the `#[EncryptableField]` attribute to the properties you want to encrypt
- add the following properties to the entity
```php
  #[ORM\Column(type: Types::TEXT)]
  protected string $encryptedData;

  #[ORM\Column(type: Types::STRING, length: 255)]
  protected string $encryptionKeyName;
```

### Symfony Messenger Integration

There are 2 ways to encrypt messages in Symfony Messenger:
- Encrypt the whole message, this will encrypt all the properties of the message. Best to use for third-party messages, where you don't have control over the message class.
- Encrypt only the required properties of the message. Best to use for your own application messages, where you have control over the message class.

First, you need to override the default the serializer in `config/packages/messenger.php`:

```php
// config/packages/messenger.php

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

For the messages you want to encrypt only the required properties, you can add the `#[EncryptableField]` attribute to the properties you want to encrypt and implement the `\EonX\EasyEncryption\Interfaces\EncryptableInterface` in the message class and add the `\EonX\EasyEncryption\Traits\EncryptableTrait` trait to it.
```php

use EonX\EasyEncryption\Attributes\EncryptableField;
use EonX\EasyEncryption\Interfaces\EncryptableInterface;
use EonX\EasyEncryption\Traits\EncryptableTrait;

final class SomeMessage implements EncryptableInterface
{
    use EncryptableTrait;

    #[EncryptableField]
    private string $message;

    #[EncryptableField]
    private string $name;
```

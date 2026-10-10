---eonx_docs---
title: Configuration
weight: 1001
---eonx_docs---

# Integrating with EasyErrorHandler and EasyBugsnag

If you are using the EasyErrorHandler package in your application the EasyApiPlatform package will
automatically integrate with it. All validation and serialization exception (related to denormalization)
will be handled by the EasyErrorHandler package.

If you are using the EasyBugsnag package in your application you could send the validation and serialization exception to Bugsnag.

## Example configuration files

In Symfony, you could have a configuration file called `easy_api_platform.php` that looks like the following:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

return App::config([
    'easy_api_platform' => [
        'easy_error_handler' => [
            'custom_serializer_exceptions' => [
                [
                    'class' => UnexpectedValueException::class,
                    'message_pattern' => '/This value is not a valid date\/time\./',
                    'violation_message' => 'violations.invalid_datetime',
                ],
                [
                    'class' => NotNormalizableValueException::class,
                    'message_pattern' => '/Failed to parse time string \(.*\) at position .* \(.*\): .*/',
                    'violation_message' => 'Some custom violation message for datetime parsing error.',
                ],
            ],
            'report_exceptions_to_bugsnag' => true,
        ],
    ],
]);


```

## Attributing a violation to a field

Violations are keyed by property path, so a response looks like this:

```json
{
    "violations": {
        "someCarbonImmutableDate": ["This value is not a valid date/time."]
    }
}
```

When the property path cannot be determined, the violation is reported against the **root property path**
- the empty string - because the payload as a whole is at fault:

```json
{
    "violations": {
        "": ["The input data is misformatted."]
    }
}
```

This is what you get for a malformed request body, and it is also what you get from a custom denormalizer
that throws an exception carrying no path.

### Write custom denormalizers so the field is preserved

Symfony puts the current field in `$context['deserialization_path']` while denormalizing an attribute, but
it only propagates that path for `NotNormalizableValueException`. Any other exception loses it, and the
violation ends up under the root property path. The path cannot be recovered afterwards - it is only
present in the stack trace arguments, which PHP discards whenever `zend.exception_ignore_args` is enabled,
as it is in `php.ini-production`.

So throw `NotNormalizableValueException` and pass the path along:

```php
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

public function denormalize(mixed $data, string $type, ?string $format = null, ?array $context = null): CarbonImmutable
{
    if (Carbon::canBeCreatedFromFormat($data, 'Y-m-d')) {
        return new CarbonImmutable($data);
    }

    /** @var string|null $deserializationPath */
    $deserializationPath = $context['deserialization_path'] ?? null;

    throw NotNormalizableValueException::createForUnexpectedDataType(
        'Custom message from custom CarbonNormalizer.',
        $data,
        [CarbonImmutable::class],
        $deserializationPath
    );
}
```

Nested paths are handled for you - denormalizing `{"author": {"bornAt": "nonsense"}}` yields
`author.bornAt`.

Note that passing the expected types also changes which message the client sees. API Platform turns the
collected error into a violation reading `This value should be of type CarbonImmutable.`, which
EasyApiPlatform then translates to `violations.invalid_datetime`. You therefore no longer need a
`custom_serializer_exceptions` entry for it - that config is for exceptions you cannot change, such as
those thrown by third-party normalizers.

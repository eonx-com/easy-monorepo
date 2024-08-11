<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Laravel;

use EonX\EasyUtils\Bundle\EasyUtilsBundle;
use EonX\EasyUtils\Bundle\Enum\ConfigTag;
use EonX\EasyUtils\Common\Trimmer\RecursiveStringTrimmer;
use EonX\EasyUtils\Common\Trimmer\StringTrimmerInterface;
use EonX\EasyUtils\CreditCard\Validator\CreditCardNumberValidator;
use EonX\EasyUtils\CreditCard\Validator\CreditCardNumberValidatorInterface;
use EonX\EasyUtils\Csv\Parser\CsvWithHeadersParser;
use EonX\EasyUtils\Csv\Parser\CsvWithHeadersParserInterface;
use EonX\EasyUtils\Laravel\Middleware\TrimStringsMiddleware;
use EonX\EasyUtils\Math\Helper\MathHelper;
use EonX\EasyUtils\Math\Helper\MathHelperInterface;
use EonX\EasyUtils\SensitiveData\Sanitizer\AuthorizationStringSanitizer;
use EonX\EasyUtils\SensitiveData\Sanitizer\CreditCardNumberStringSanitizer;
use EonX\EasyUtils\SensitiveData\Sanitizer\JsonStringSanitizer;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizer;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\SensitiveData\Sanitizer\StringSanitizerInterface;
use EonX\EasyUtils\SensitiveData\Sanitizer\UrlStringSanitizer;
use EonX\EasyUtils\SensitiveData\Transformer\DefaultObjectTransformer;
use EonX\EasyUtils\SensitiveData\Transformer\ThrowableObjectTransformer;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

final class EasyUtilsServiceProvider extends ServiceProvider
{
    private const STRING_SANITIZER_DEFAULT_PRIORITY = 10000;

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-utils.php' => \base_path('config/easy-utils.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-utils.php', 'easy-utils');

        $this->app->singleton(
            CreditCardNumberValidatorInterface::class,
            static fn (): CreditCardNumberValidatorInterface => new CreditCardNumberValidator()
        );

        $this->app->singleton(
            CsvWithHeadersParserInterface::class,
            static fn (): CsvWithHeadersParserInterface => new CsvWithHeadersParser()
        );

        $this->app->singleton(MathHelperInterface::class, static fn (): MathHelperInterface => new MathHelper(
            roundPrecision: \config('easy-utils.math.round-precision'),
            roundMode: \config('easy-utils.math.round-mode'),
            scale: \config('easy-utils.math.scale'),
            decimalSeparator: \config('easy-utils.math.format-decimal-separator'),
            thousandsSeparator: \config('easy-utils.math.format-thousands-separator')
        ));

        $this->sensitiveData();
        $this->stringTrimmer();
    }

    private function sensitiveData(): void
    {
        if ((bool)\config('easy-utils.sensitive_data.enabled', true) === false) {
            return;
        }

        if (\config('easy-utils.sensitive_data.use_default_object_transformers', true)) {
            $this->app->singleton(
                ThrowableObjectTransformer::class,
                static fn (): ThrowableObjectTransformer => new ThrowableObjectTransformer(100)
            );
            $this->app->tag(
                ThrowableObjectTransformer::class,
                [ConfigTag::SensitiveDataObjectTransformer->value]
            );

            $this->app->singleton(
                DefaultObjectTransformer::class,
                static fn (): DefaultObjectTransformer => new DefaultObjectTransformer(10000)
            );
            $this->app->tag(
                DefaultObjectTransformer::class,
                [ConfigTag::SensitiveDataObjectTransformer->value]
            );
        }

        if (\config('easy-utils.sensitive_data.use_default_string_sanitizers', true)) {
            $this->app->singleton(
                AuthorizationStringSanitizer::class,
                static fn (): StringSanitizerInterface => new AuthorizationStringSanitizer(
                    self::STRING_SANITIZER_DEFAULT_PRIORITY
                )
            );
            $this->app->tag(AuthorizationStringSanitizer::class, [
                ConfigTag::SensitiveDataStringSanitizer->value,
            ]);

            $this->app->singleton(
                CreditCardNumberStringSanitizer::class,
                static fn (Container $app): StringSanitizerInterface => new CreditCardNumberStringSanitizer(
                    $app->make(CreditCardNumberValidatorInterface::class),
                    self::STRING_SANITIZER_DEFAULT_PRIORITY
                )
            );
            $this->app->tag(CreditCardNumberStringSanitizer::class, [
                ConfigTag::SensitiveDataStringSanitizer->value,
            ]);

            $this->app->singleton(
                JsonStringSanitizer::class,
                static fn (): StringSanitizerInterface => new JsonStringSanitizer(
                    self::STRING_SANITIZER_DEFAULT_PRIORITY
                )
            );
            $this->app->tag(JsonStringSanitizer::class, [
                ConfigTag::SensitiveDataStringSanitizer->value,
            ]);

            $this->app->singleton(
                UrlStringSanitizer::class,
                static fn (): StringSanitizerInterface => new UrlStringSanitizer(
                    self::STRING_SANITIZER_DEFAULT_PRIORITY
                )
            );
            $this->app->tag(UrlStringSanitizer::class, [
                ConfigTag::SensitiveDataStringSanitizer->value,
            ]);
        }

        $defaultKeysToMask = \config('easy-utils.sensitive_data.use_default_keys_to_mask', true)
            ? EasyUtilsBundle::SENSITIVE_DATA_DEFAULT_KEYS_TO_MASK
            : [];

        $keysToMask = \config('easy-utils.sensitive_data.keys_to_mask', []);

        $maskPattern = \config('easy-utils.sensitive_data.mask_pattern');

        $this->app->singleton(
            SensitiveDataSanitizerInterface::class,
            static fn (Container $container): SensitiveDataSanitizerInterface => new SensitiveDataSanitizer(
                \array_unique(\array_merge($defaultKeysToMask, $keysToMask)),
                $maskPattern,
                $container->tagged(ConfigTag::SensitiveDataObjectTransformer->value),
                $container->tagged(ConfigTag::SensitiveDataStringSanitizer->value)
            )
        );
    }

    private function stringTrimmer(): void
    {
        if ((bool)\config('easy-utils.string_trimmer.enabled', false) === false) {
            return;
        }

        /** @var \Laravel\Lumen\Application $app */
        $app = $this->app;
        $app->singleton(StringTrimmerInterface::class, RecursiveStringTrimmer::class);
        $app->singleton(
            TrimStringsMiddleware::class,
            static fn (Container $app): TrimStringsMiddleware => new TrimStringsMiddleware(
                $app->get(StringTrimmerInterface::class),
                \config('easy-utils.string_trimmer.except_keys', [])
            )
        );
        $app->middleware([TrimStringsMiddleware::class]);
    }
}

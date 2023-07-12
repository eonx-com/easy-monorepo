<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Laravel;

use EonX\EasyUtils\Bridge\BridgeConstantsInterface;
use EonX\EasyUtils\Bridge\Laravel\Middlewares\TrimStrings;
use EonX\EasyUtils\CreditCard\CreditCardNumberValidator;
use EonX\EasyUtils\CreditCard\CreditCardNumberValidatorInterface;
use EonX\EasyUtils\Csv\CsvWithHeadersParser;
use EonX\EasyUtils\Csv\CsvWithHeadersParserInterface;
use EonX\EasyUtils\Interfaces\MathInterface;
use EonX\EasyUtils\Math\Math;
use EonX\EasyUtils\SensitiveData\ObjectTransformers\DefaultObjectTransformer;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizer;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;
use EonX\EasyUtils\SensitiveData\StringSanitizerInterface;
use EonX\EasyUtils\SensitiveData\StringSanitizers\AuthorizationStringSanitizer;
use EonX\EasyUtils\SensitiveData\StringSanitizers\CreditCardNumberStringSanitizer;
use EonX\EasyUtils\SensitiveData\StringSanitizers\JsonStringSanitizer;
use EonX\EasyUtils\SensitiveData\StringSanitizers\UrlStringSanitizer;
use EonX\EasyUtils\StringTrimmers\RecursiveStringTrimmer;
use EonX\EasyUtils\StringTrimmers\StringTrimmerInterface;
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
            static function (): CreditCardNumberValidatorInterface {
                return new CreditCardNumberValidator();
            }
        );

        $this->app->singleton(
            CsvWithHeadersParserInterface::class,
            static function (): CsvWithHeadersParserInterface {
                return new CsvWithHeadersParser();
            }
        );

        $this->app->singleton(MathInterface::class, static function (): MathInterface {
            return new Math(
                roundPrecision: \config('easy-utils.math.round-precision'),
                roundMode: \config('easy-utils.math.round-mode'),
                scale: \config('easy-utils.math.scale'),
                decimalSeparator: \config('easy-utils.math.format-decimal-separator'),
                thousandsSeparator: \config('easy-utils.math.format-thousands-separator')
            );
        });

        $this->sensitiveData();
        $this->stringTrimmer();
    }

    private function sensitiveData(): void
    {
        if ((bool)\config('easy-utils.sensitive_data.enabled', false) === false) {
            return;
        }

        if (\config('easy-utils.sensitive_data.use_default_object_transformers', true)) {
            $this->app->singleton(
                DefaultObjectTransformer::class,
                static function (): DefaultObjectTransformer {
                    return new DefaultObjectTransformer(10000);
                }
            );
            $this->app->tag(
                DefaultObjectTransformer::class,
                [BridgeConstantsInterface::TAG_SENSITIVE_DATA_OBJECT_TRANSFORMER]
            );
        }

        if (\config('easy-utils.sensitive_data.use_default_string_sanitizers', true)) {
            $this->app->singleton(
                AuthorizationStringSanitizer::class,
                static function (): StringSanitizerInterface {
                    return new AuthorizationStringSanitizer(self::STRING_SANITIZER_DEFAULT_PRIORITY);
                }
            );
            $this->app->tag(AuthorizationStringSanitizer::class, [
                BridgeConstantsInterface::TAG_SENSITIVE_DATA_STRING_SANITIZER,
            ]);

            $this->app->singleton(
                CreditCardNumberStringSanitizer::class,
                static function (Container $app): StringSanitizerInterface {
                    return new CreditCardNumberStringSanitizer(
                        $app->make(CreditCardNumberValidatorInterface::class),
                        self::STRING_SANITIZER_DEFAULT_PRIORITY
                    );
                }
            );
            $this->app->tag(CreditCardNumberStringSanitizer::class, [
                BridgeConstantsInterface::TAG_SENSITIVE_DATA_STRING_SANITIZER,
            ]);

            $this->app->singleton(
                JsonStringSanitizer::class,
                static function (): StringSanitizerInterface {
                    return new JsonStringSanitizer(self::STRING_SANITIZER_DEFAULT_PRIORITY);
                }
            );
            $this->app->tag(JsonStringSanitizer::class, [
                BridgeConstantsInterface::TAG_SENSITIVE_DATA_STRING_SANITIZER,
            ]);

            $this->app->singleton(
                UrlStringSanitizer::class,
                static function (): StringSanitizerInterface {
                    return new UrlStringSanitizer(self::STRING_SANITIZER_DEFAULT_PRIORITY);
                }
            );
            $this->app->tag(UrlStringSanitizer::class, [
                BridgeConstantsInterface::TAG_SENSITIVE_DATA_STRING_SANITIZER,
            ]);
        }

        $this->app->singleton(
            SensitiveDataSanitizerInterface::class,
            static function (Container $container): SensitiveDataSanitizerInterface {
                return new SensitiveDataSanitizer(
                    \config('easy-utils.sensitive_data.use_default_keys_to_mask', true),
                    \config('easy-utils.sensitive_data.keys_to_mask', []),
                    \config('easy-utils.sensitive_data.mask_pattern'),
                    $container->tagged(BridgeConstantsInterface::TAG_SENSITIVE_DATA_OBJECT_TRANSFORMER),
                    $container->tagged(BridgeConstantsInterface::TAG_SENSITIVE_DATA_STRING_SANITIZER)
                );
            }
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
        $app->singleton(TrimStrings::class, static function (Container $app): TrimStrings {
            return new TrimStrings(
                $app->get(StringTrimmerInterface::class),
                \config('easy-utils.string_trimmer.except_keys', [])
            );
        });
        $app->middleware([TrimStrings::class]);
    }
}

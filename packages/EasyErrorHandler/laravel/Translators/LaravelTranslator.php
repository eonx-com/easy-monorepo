<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Laravel\Translators;

use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use EonX\EasyErrorHandler\Laravel\Enums\TranslationParam;
use Illuminate\Contracts\Translation\Translator as IlluminateTranslatorInterface;

final readonly class LaravelTranslator implements TranslatorInterface
{
    public function __construct(
        private IlluminateTranslatorInterface $decorated,
    ) {
    }

    public function trans(string $message, array $parameters, ?string $locale = null): string
    {
        $translation = $this->doTranslate($message, $parameters, $locale);

        if ($translation !== $message) {
            return $translation;
        }

        $namespacedMessage = \sprintf('%s::%s', TranslationParam::Namespace->value, \trim($message));
        $translation = $this->doTranslate($namespacedMessage, $parameters, $locale);

        // If translation is finally different we return it otherwise default to original message
        return $translation !== $namespacedMessage ? $translation : $message;
    }

    private function doTranslate(string $message, array $parameters, ?string $locale = null): string
    {
        $method = \method_exists($this->decorated, 'lang') ? 'lang' : 'get';

        return $this->decorated->{$method}(\trim($message), $parameters, $locale);
    }
}

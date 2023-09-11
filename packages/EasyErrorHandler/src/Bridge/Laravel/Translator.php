<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Illuminate\Contracts\Translation\Translator as IlluminateTranslatorInterface;

final class Translator implements TranslatorInterface
{
    public function __construct(
        private readonly IlluminateTranslatorInterface $decorated,
    ) {
    }

    public function trans(string $message, array $parameters, ?string $locale = null): string
    {
        $translation = $this->doTranslate($message, $parameters, $locale);

        if ($translation !== $message) {
            return $translation;
        }

        $namespacedMessage = \sprintf('%s::%s', BridgeConstantsInterface::TRANSLATION_NAMESPACE, \trim($message));
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

<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Illuminate\Contracts\Translation\Translator as IlluminateTranslatorInterface;

final class Translator implements TranslatorInterface
{
    /**
     * @var \Illuminate\Contracts\Translation\Translator
     */
    private $decorated;

    public function __construct(IlluminateTranslatorInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param mixed[] $parameters
     */
    public function trans(string $message, array $parameters): string
    {
        $translation = $this->doTrans($message, $parameters, false);

        if ($translation !== $message) {
            return $translation;
        }

        $namespaceTranslation = $this->doTrans($message, $parameters);

        if ($this->processMessage($translation) === $namespaceTranslation) {
            return $translation;
        }

        return $namespaceTranslation;
    }

    /**
     * @param mixed[] $parameters
     */
    private function doTrans(string $message, array $parameters, bool $hasPrefix = true): string
    {
        $method = \method_exists($this->decorated, 'lang') ? 'lang' : 'get';

        return $this->decorated->{$method}($this->processMessage($message, $hasPrefix), $parameters);
    }

    private function processMessage(string $message, bool $hasPrefix = true): string
    {
        return $hasPrefix ? \sprintf(
            '%s::%s',
            BridgeConstantsInterface::TRANSLATION_NAMESPACE,
            \trim($message)
        ) : \trim($message);
    }
}

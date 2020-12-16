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
        $translation = $this->doTrans($message, $parameters);

        if ($translation !== $message) {
            return $translation;
        }

        $namespacedMessage = \sprintf('%s::%s', BridgeConstantsInterface::TRANSLATION_NAMESPACE, \trim($message));
        $translation = $this->doTrans($namespacedMessage, $parameters);

        // If translation is finally different we return it otherwise default to original message.
        return $translation !== $namespacedMessage ? $translation : $message;
    }

    /**
     * @param mixed[] $parameters
     */
    private function doTrans(string $message, array $parameters): string
    {
        $method = \method_exists($this->decorated, 'lang') ? 'lang' : 'get';

        return $this->decorated->{$method}(\trim($message), $parameters);
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony;

use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;

final class Translator implements TranslatorInterface
{
    public function __construct(
        private readonly SymfonyTranslatorInterface $decorated,
        private readonly ?string $domain = null
    ) {
    }

    /**
     * @param mixed[] $parameters
     */
    public function trans(string $message, array $parameters, ?string $locale = null): string
    {
        $translation = $this->decorated->trans($message, $parameters, $this->domain, $locale);

        if ($translation !== $message) {
            return $translation;
        }

        return $this->decorated->trans($message, $parameters, 'EasyErrorHandlerBundle', $locale);
    }
}

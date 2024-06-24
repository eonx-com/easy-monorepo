<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony;

use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;

final readonly class Translator implements TranslatorInterface
{
    private const DEFAULT_DOMAIN = 'EasyErrorHandlerBundle';

    public function __construct(
        private SymfonyTranslatorInterface $decorated,
        private ?string $domain = null,
    ) {
    }

    public function trans(string $message, array $parameters, ?string $locale = null): string
    {
        $translation = $this->decorated->trans($message, $parameters, $this->domain, $locale);

        if ($translation !== $message) {
            return $translation;
        }

        return $this->decorated->trans($message, $parameters, self::DEFAULT_DOMAIN, $locale);
    }
}

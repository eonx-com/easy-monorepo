<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Translator;

use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;

final readonly class Translator implements TranslatorInterface
{
    private const string DEFAULT_DOMAIN = 'EasyErrorHandlerBundle';

    public function __construct(
        // @todo: Rename to `$translator` in next major release (7.0) as it's not a decoration
        private SymfonyTranslatorInterface&TranslatorBagInterface $decorated,
        private ?string $domain = null,
    ) {
    }

    public function trans(string $message, array $parameters, ?string $locale = null): string
    {
        $catalogue = $this->decorated->getCatalogue();
        if (
            $catalogue->has($message, self::DEFAULT_DOMAIN) === false
            || $catalogue->has($message, $this->domain ?? 'messages')
        ) {
            return $this->decorated->trans($message, $parameters, $this->domain, $locale);
        }

        return $this->decorated->trans($message, $parameters, self::DEFAULT_DOMAIN, $locale);
    }
}

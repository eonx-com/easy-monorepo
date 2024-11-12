<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Translator;

use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;

final readonly class Translator implements TranslatorInterface
{
    private const DEFAULT_DOMAIN = 'EasyErrorHandlerBundle';

    public function __construct(
        private SymfonyTranslatorInterface $translator,
        private ?string $domain = null,
    ) {
    }

    public function trans(string $message, array $parameters, ?string $locale = null): string
    {
        /** @var \Symfony\Component\Translation\TranslatorBagInterface $translatorBag */
        $translatorBag = $this->translator;
        $catalogue = $translatorBag->getCatalogue();
        if (
            $catalogue->has($message, self::DEFAULT_DOMAIN) === false
            || ($catalogue->has($message, self::DEFAULT_DOMAIN)
            && $catalogue->has($message, $this->domain ?? 'messages'))
        ) {
            return $this->translator->trans($message, $parameters, $this->domain, $locale);
        }

        return $this->translator->trans($message, $parameters, self::DEFAULT_DOMAIN, $locale);
    }
}

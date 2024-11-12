<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Translator;

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
        /** @var \EonX\EasyErrorHandler\Common\Translator\TranslatorInterface|\Symfony\Component\Translation\TranslatorBagInterface $translator */
        $translator = $this->decorated;
        $catalogue = $translator->getCatalogue($locale);
        if ($catalogue->has($message, self::DEFAULT_DOMAIN) && $catalogue->has($message, $this->domain)) {
            return $this->decorated->trans($message, $parameters, $this->domain, $locale);
        }

        if ($catalogue->has($message, self::DEFAULT_DOMAIN) === false) {
            return $this->decorated->trans($message, $parameters, $this->domain, $locale);
        }

        return $this->decorated->trans($message, $parameters, self::DEFAULT_DOMAIN, $locale);
    }
}

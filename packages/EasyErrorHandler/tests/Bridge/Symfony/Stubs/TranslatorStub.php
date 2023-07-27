<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslatorStub implements TranslatorInterface
{
    private const LOCALE = 'en';

    /**
     * @var mixed[]|null
     */
    private ?array $translations = null;

    private ?Translator $translator = null;

    public function getLocale(): string
    {
        return self::LOCALE;
    }

    /**
     * @param mixed[] $translations
     */
    public function setTranslations(array $translations): void
    {
        $this->translations = $translations;
    }

    /**
     * @param mixed[]|null $parameters
     */
    public function trans(string $id, ?array $parameters = null, ?string $domain = null, ?string $locale = null): string
    {
        $translated = $this->getTranslator()
            ->trans($id, $parameters ?? [], $domain, $locale);

        // TODO - That's cheating... Translations need to be reworked completely
        if (\count($parameters ?? []) > 0) {
            $translated = \str_replace(':', '', $translated);
        }

        return $translated;
    }

    private function getTranslator(): TranslatorInterface
    {
        if ($this->translator !== null) {
            return $this->translator;
        }

        $translator = new Translator(self::LOCALE);
        $translator->addLoader('array', new ArrayLoader());

        if ($this->translations !== null) {
            $translator->addResource('array', $this->translations, self::LOCALE, 'EasyErrorHandlerBundle');
        }

        $this->translator = $translator;

        return $this->translator;
    }
}

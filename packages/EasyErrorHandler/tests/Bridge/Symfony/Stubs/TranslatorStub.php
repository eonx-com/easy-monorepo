<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslatorStub implements TranslatorInterface
{
    /**
     * @var mixed[]
     */
    private $translations;

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    private $translator;

    /**
     * @param mixed[] $translations
     */
    public function setTranslations(array $translations): void
    {
        $this->translations = $translations;
    }

    /**
     * @param string $id
     * @param null|mixed[] $parameters
     * @param null|string $domain
     * @param null|string $locale
     */
    public function trans($id, ?array $parameters = null, $domain = null, $locale = null): string
    {
        $translated = $this->getTranslator()->trans($id, $parameters ?? [], $domain, $locale);

        // TODO - That's cheating... Translations need to be reworked completely
        if (empty($parameters) === false) {
            $translated = \str_replace(':', '', $translated);
        }

        return $translated;
    }

    private function getTranslator(): TranslatorInterface
    {
        if ($this->translator !== null) {
            return $this->translator;
        }

        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());

        if ($this->translations !== null) {
            $translator->addResource('array', $this->translations, 'en', 'messages');
        }

        return $this->translator = $translator;
    }
}

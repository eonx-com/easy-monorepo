<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony;

use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;

final class Translator implements TranslatorInterface
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $decorated;

    /**
     * @var null|string
     */
    private $domain;

    public function __construct(SymfonyTranslatorInterface $decorated, ?string $domain = null)
    {
        $this->decorated = $decorated;
        $this->domain = $domain;
    }

    /**
     * @param mixed[] $parameters
     */
    public function trans(string $message, array $parameters): string
    {
        return $this->decorated->trans($message, $parameters, $this->domain);
    }
}

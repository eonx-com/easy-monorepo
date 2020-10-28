<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Traits;

use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorAwareTrait
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * @required
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}

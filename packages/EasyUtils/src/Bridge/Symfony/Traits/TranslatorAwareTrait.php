<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\Traits;

use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorAwareTrait
{
    protected TranslatorInterface $translator;

    #[Required]
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}

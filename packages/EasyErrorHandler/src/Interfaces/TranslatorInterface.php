<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface TranslatorInterface
{
    public function trans(string $message, array $parameters, ?string $locale = null): string;
}

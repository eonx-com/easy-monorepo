<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Stubs;

use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;

final class TranslatorStub implements TranslatorInterface
{
    private array $translated = [];

    public function getTranslatedMessages(): array
    {
        return $this->translated;
    }

    public function trans(string $message, array $parameters, ?string $locale = null): string
    {
        $this->translated[] = [
            'message' => $message,
            'parameters' => $parameters,
            'locale' => $locale,
        ];

        return $message;
    }
}

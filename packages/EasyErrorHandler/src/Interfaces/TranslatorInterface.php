<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface TranslatorInterface
{
    /**
     * @param mixed[] $parameters
     * @param null|mixed[] $options Additional options for bridge implementations
     */
    public function trans(string $message, array $parameters, ?array $options = null): string;
}

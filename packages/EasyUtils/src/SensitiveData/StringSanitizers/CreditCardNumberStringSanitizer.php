<?php

declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\StringSanitizers;

use EonX\EasyUtils\CreditCard\CreditCardNumberValidatorInterface;

final class CreditCardNumberStringSanitizer extends AbstractStringSanitizer
{
    public function __construct(
        private CreditCardNumberValidatorInterface $creditCardNumberValidator,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    /**
     * @param mixed[] $keysToMask
     */
    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        /** @var string[][] $matches */
        $matches = [];
        $matched = \preg_match_all('/(\d[^A-Za-z&="\'<]*){12,}/', $string, $matches);

        if ($matched === 0 || $matched === false) {
            return $string;
        }

        // Mask potentially unmasked credit card numbers anywhere else
        foreach ($matches as $match) {
            if ($this->creditCardNumberValidator->isCreditCardNumberValid($match[0]) === false) {
                continue;
            }

            $lastSymbol = \str_ends_with($match[0], '\\') ? '\\' : '';

            $replace = \preg_replace(
                '/^(\d{6}).+(\d{4})$/',
                '$1' . $maskPattern . '$2',
                \preg_replace('/[\D]/', '', $match) ?? ''
            );

            $string = \str_replace($match, ($replace[0] ?? '') . $lastSymbol, $string);
        }

        return $string;
    }
}

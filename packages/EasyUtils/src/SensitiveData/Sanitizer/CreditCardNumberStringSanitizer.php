<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

use EonX\EasyUtils\CreditCard\Validator\CreditCardNumberValidatorInterface;

final class CreditCardNumberStringSanitizer extends AbstractStringSanitizer
{
    public function __construct(
        private readonly CreditCardNumberValidatorInterface $creditCardNumberValidator,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        $matches = [];
        $matched = \preg_match_all('/(\d[^A-Za-z&="\'<]*){12,}/', $string, $matches);

        if ($matched === 0 || $matched === false) {
            return $string;
        }

        // Mask every credit card number found in the string. Iterate over the full
        // matches ($matches[0]); each candidate is validated and masked independently,
        // so a card is still masked when it follows a non-card digit sequence, and each
        // card is replaced with its own masked value.
        /** @var string[] $fullMatches */
        $fullMatches = $matches[0];

        foreach ($fullMatches as $candidate) {
            // The greedy pattern also swallows any separators trailing the last digit
            // (spaces, dots, backslashes, ...). Only the digit "core" is the candidate
            // card number and the only part we replace, so trailing characters are kept
            $core = (string)\preg_replace('/\D+$/', '', $candidate);

            if ($this->creditCardNumberValidator->isCreditCardNumberValid($core) === false) {
                continue;
            }

            $replace = \preg_replace(
                '/^(\d{6}).+(\d{4})$/',
                '$1' . $maskPattern . '$2',
                (string)\preg_replace('/\D/', '', $core)
            );

            $string = \str_replace($core, (string)$replace, $string);
        }

        return $string;
    }
}

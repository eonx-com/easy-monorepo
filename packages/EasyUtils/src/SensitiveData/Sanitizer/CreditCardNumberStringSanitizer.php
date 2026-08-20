<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

use EonX\EasyUtils\CreditCard\Validator\CreditCardNumberValidatorInterface;

final class CreditCardNumberStringSanitizer extends AbstractStringSanitizer
{
    /**
     * A card candidate: >= 12 digits where the only characters allowed *between* digits are
     * visual grouping ones (space, dot, dash, backslash). Everything else — comma, newline,
     * tab, letters, symbols, ... — terminates the candidate, so adjacent numbers/cards/values
     * are not merged into one long run that fails validation as a single card. The pattern
     * also starts and ends on a digit, so a candidate never carries surrounding separators
     */
    private const CARD_CANDIDATE_PATTERN = '/\d(?:[ .\\\\-]*\d){11,}/';

    public function __construct(
        private readonly CreditCardNumberValidatorInterface $creditCardNumberValidator,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        $matches = [];
        $matched = \preg_match_all(self::CARD_CANDIDATE_PATTERN, $string, $matches);

        if ($matched === 0 || $matched === false) {
            return $string;
        }

        // Validate and mask each candidate independently, replacing it with its own masked value
        /** @var string[] $candidates */
        $candidates = $matches[0];

        foreach ($candidates as $candidate) {
            if ($this->creditCardNumberValidator->isCreditCardNumberValid($candidate) === false) {
                continue;
            }

            $replace = \preg_replace(
                '/^(\d{6}).+(\d{4})$/',
                '${1}' . $this->escapeForReplacement($maskPattern) . '${2}',
                (string)\preg_replace('/\D/', '', $candidate)
            );

            $string = \str_replace($candidate, (string)$replace, $string);
        }

        return $string;
    }
}

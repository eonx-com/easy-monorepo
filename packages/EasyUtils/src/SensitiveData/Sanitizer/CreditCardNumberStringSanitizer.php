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

    /**
     * A UUID in its canonical 8-4-4-4-12 hex form that does not touch other letters or digits.
     * The digit groups of a UUID would otherwise join through the dashes into a card candidate
     * (e.g. "1f1dc398-654c-6538-9652-0579065566b8" gives "6538-9652-0579065566"), and about one
     * such candidate in ten passes the card validation. A card number has 12-19 digits, so it
     * can never have this form, and skipping UUIDs cannot hide a real card number
     */
    private const UUID_PATTERN = '/(?<![0-9a-z])[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}(?![0-9a-z])/i';

    public function __construct(
        private readonly CreditCardNumberValidatorInterface $creditCardNumberValidator,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        $uuids = [];
        $matched = \preg_match_all(self::UUID_PATTERN, $string, $uuids, \PREG_OFFSET_CAPTURE);

        $result = '';
        $offset = 0;

        // UUIDs are kept as they are, only the text between them is scanned for card numbers. On a PCRE
        // failure (e.g. a resource limit) no UUID is skipped, and the whole string is scanned
        foreach ($matched > 0 ? $uuids[0] : [] as [$uuid, $position]) {
            $result .= $this->sanitizeCardNumbers(\substr($string, $offset, $position - $offset), $maskPattern);
            $result .= $uuid;
            $offset = $position + \strlen($uuid);
        }

        return $result . $this->sanitizeCardNumbers(\substr($string, $offset), $maskPattern);
    }

    private function sanitizeCardNumbers(string $string, string $maskPattern): string
    {
        // Validate and mask each candidate independently, replacing it with its own masked value
        $result = \preg_replace_callback(
            self::CARD_CANDIDATE_PATTERN,
            function (array $matches) use ($maskPattern): string {
                $candidate = $matches[0];

                if ($this->creditCardNumberValidator->isCreditCardNumberValid($candidate) === false) {
                    return $candidate;
                }

                return (string)\preg_replace(
                    '/^(\d{6}).+(\d{4})$/',
                    '${1}' . $this->escapeForReplacement($maskPattern) . '${2}',
                    (string)\preg_replace('/\D/', '', $candidate)
                );
            },
            $string
        );

        // On a PCRE failure (e.g. a resource limit) the text is kept as it is instead of being lost
        return $result ?? $string;
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

use EonX\EasyUtils\CreditCard\Validator\CreditCardNumberValidatorInterface;

final class CreditCardNumberStringSanitizer extends AbstractStringSanitizer
{
    /**
     * A card candidate: a run of digits where the only characters allowed between digits are
     * visual grouping ones (space, dot, dash, backslash). Everything else — comma, newline,
     * tab, letters, symbols, ... — terminates the candidate, so adjacent numbers/cards/values
     * are not merged into one long run. The pattern has no nested repetition and the class is
     * possessive, so a long run cannot exhaust the PCRE (JIT) stack. Trailing separators and
     * runs shorter than a card are handled in the callback
     */
    private const CARD_CANDIDATE_PATTERN = '/\d[\d .\\\\-]*+/';

    private const CARD_GROUPS_MAX = 5;

    private const CARD_LENGTH_MAX = 19;

    private const CARD_LENGTH_MIN = 12;

    private const DIGITS = '0123456789';

    private const SEPARATORS = ' .\\-';

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
        \preg_match_all(self::UUID_PATTERN, $string, $uuids, \PREG_OFFSET_CAPTURE);

        $result = '';
        $offset = 0;

        // UUIDs are kept as they are, only the text between them is scanned for card numbers. On a PCRE
        // failure only the UUIDs found before it are skipped, and the rest of the string is scanned
        foreach ($uuids[0] as [$uuid, $position]) {
            $result .= $this->sanitizeCardNumbers(\substr($string, $offset, $position - $offset), $maskPattern);
            $result .= $uuid;
            $offset = $position + \strlen($uuid);
        }

        return $result . $this->sanitizeCardNumbers(\substr($string, $offset), $maskPattern);
    }

    /**
     * Keeps the first 6 and the last 4 digits. No regex here, so a PCRE failure cannot drop the text
     */
    private function maskCardNumber(string $cardNumber, string $maskPattern): string
    {
        $digits = $this->removeSeparators($cardNumber);

        return \substr($digits, 0, 6) . $maskPattern . \substr($digits, -4);
    }

    private function removeSeparators(string $candidate): string
    {
        return \str_replace(\str_split(self::SEPARATORS), '', $candidate);
    }

    /**
     * Masks the card numbers inside a candidate that is not a card number as a whole, e.g. a card
     * followed by another number ("4005 5500 0000 0001 1225") or two cards ("4005550000000001 5313581000123430").
     * From each digit group, the longest run of whole groups (at most 5 groups, 12-19 digits) that is a valid
     * card number is masked, everything else is kept as it is. The work per group is bounded, so the scan is linear
     */
    private function sanitizeCardNumberGroups(string $candidate, string $maskPattern): string
    {
        $length = \strlen($candidate);
        $position = 0;
        $result = '';

        while ($position < $length) {
            $cardEnd = null;
            $cursor = $position;
            $digitCount = 0;

            for ($groups = 1; $groups <= self::CARD_GROUPS_MAX && $cursor < $length; $groups++) {
                $groupLength = \strspn($candidate, self::DIGITS, $cursor);
                $groupEnd = $cursor + $groupLength;
                $digitCount += $groupLength;

                if ($digitCount > self::CARD_LENGTH_MAX) {
                    break;
                }

                // The validator ignores the separators between the groups
                if ($digitCount >= self::CARD_LENGTH_MIN && $this->creditCardNumberValidator->isCreditCardNumberValid(
                    \substr($candidate, $position, $groupEnd - $position)
                )) {
                    $cardEnd = $groupEnd;
                }

                $cursor = $groupEnd + \strspn($candidate, self::SEPARATORS, $groupEnd);
            }

            $partEnd = $cardEnd ?? $position + \strspn($candidate, self::DIGITS, $position);
            $result .= $cardEnd === null
                ? \substr($candidate, $position, $partEnd - $position)
                : $this->maskCardNumber(\substr($candidate, $position, $partEnd - $position), $maskPattern);

            $position = $partEnd + \strspn($candidate, self::SEPARATORS, $partEnd);
            $result .= \substr($candidate, $partEnd, $position - $partEnd);
        }

        return $result;
    }

    private function sanitizeCardNumbers(string $string, string $maskPattern): string
    {
        // Validate and mask each candidate independently, replacing it with its own masked value
        $result = \preg_replace_callback(
            self::CARD_CANDIDATE_PATTERN,
            function (array $matches) use ($maskPattern): string {
                $candidate = \rtrim($matches[0], self::SEPARATORS);
                $trailingSeparators = \substr($matches[0], \strlen($candidate));

                if (\strlen($this->removeSeparators($candidate)) < self::CARD_LENGTH_MIN) {
                    return $matches[0];
                }

                $sanitized = $this->creditCardNumberValidator->isCreditCardNumberValid($candidate)
                    ? $this->maskCardNumber($candidate, $maskPattern)
                    : $this->sanitizeCardNumberGroups($candidate, $maskPattern);

                return $sanitized . $trailingSeparators;
            },
            $string
        );

        // On a PCRE failure (e.g. a resource limit) the text is kept as it is instead of being lost
        return $result ?? $string;
    }
}

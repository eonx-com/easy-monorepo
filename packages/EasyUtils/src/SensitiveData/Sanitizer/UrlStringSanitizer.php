<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

final class UrlStringSanitizer extends AbstractStringSanitizer
{
    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        // Braced back-reference (${1}) so a mask pattern that starts with a digit is not merged
        // into a higher-numbered reference (e.g. $1 + "2" => $12)
        $replacement = '${1}' . $this->escapeForReplacement($maskPattern);

        foreach ($keysToMask as $key) {
            $key = (string)$key;

            if ($key === '') {
                continue;
            }

            // Keys come from configuration and are interpolated into the pattern, so escape any
            // regex metacharacters (and the "/" delimiter) to keep matching literal and safe.
            // Runs of word separators are optional and interchangeable, so "api_key" also matches
            // "api-key", "api key", "api__key" and "apiKey". Leading/trailing separators are kept
            // as optional runs, so the configured spelling itself always matches
            $parts = \preg_split('/[ _-]+/', $key) ?: [$key];
            $quotedKey = \implode(
                '[ _-]*',
                \array_map(static fn (string $part): string => \preg_quote($part, '/'), $parts)
            );

            // A key made of separators only would turn into a pattern matching an empty key
            if (\implode('', $parts) === '') {
                $quotedKey = \preg_quote($key, '/');
            }

            // The "x" prefix of the key is optional where the key starts right after a delimiter
            // ("[key]=" and "/key/"), so "shared_key" also matches "[x-shared-key]=" and
            // "/x-shared-key/". Plain "key=" has no left boundary and matches the prefix as is
            $prefixedQuotedKey = '(?:x[ _-]*)?' . $quotedKey;

            // Query/array style (key=..., [key]=...): the value ends at whitespace or a structural
            // delimiter (& ? # , ; " ' ) ] } < >) but may legitimately contain "/" (e.g. base64
            // tokens). Stopping at the field boundary avoids eating the rest of a log line or
            // neighbouring fields when the value is embedded in free text
            $string = (string)\preg_replace(
                \sprintf('/(%s=|\[%s\]=)([^\s&?#,;"\')\]}<>]+)/i', $quotedKey, $prefixedQuotedKey),
                $replacement,
                $string
            );

            // Path style (/key/...): the value is a single path segment, so "/" terminates it
            $string = (string)\preg_replace(
                \sprintf('/(\/%s\/)([^&\/?#]+)/i', $prefixedQuotedKey),
                $replacement,
                $string
            );
        }

        return $string;
    }
}

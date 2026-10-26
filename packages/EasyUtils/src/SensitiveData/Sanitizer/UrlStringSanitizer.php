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
            // Separator runs are optional and interchangeable ("api_key" matches "api-key", "apiKey"),
            // edge ones are kept so the configured spelling itself always matches
            $parts = \preg_split('/[ _-]+/', $key) ?: [$key];
            $quotedKey = \implode(
                '[ _-]*',
                \array_map(static fn (string $part): string => \preg_quote($part, '/'), $parts)
            );

            // A separator-only key would otherwise produce a pattern matching an empty key
            if (\implode('', $parts) === '') {
                $quotedKey = \preg_quote($key, '/');
            }

            // Optional "x" prefix for "[key]=" and "/key/"; plain "key=" has no left boundary and already matches it
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

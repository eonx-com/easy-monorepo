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
            // Keys come from configuration and are interpolated into the pattern, so escape any
            // regex metacharacters (and the "/" delimiter) to keep matching literal and safe.
            // Word separators are optional and interchangeable, so "api_key" also matches
            // "api-key", "api key" and "apiKey"
            $quotedKey = \implode(
                '[ _-]?',
                \array_map(
                    static fn (string $part): string => \preg_quote($part, '/'),
                    \preg_split('/[ _-]+/', (string)$key, -1, \PREG_SPLIT_NO_EMPTY) ?: []
                )
            );

            if ($quotedKey === '') {
                continue;
            }

            // Query/array style (key=..., [key]=...): the value ends at whitespace or a structural
            // delimiter (& ? # , ; " ' ) ] } < >) but may legitimately contain "/" (e.g. base64
            // tokens). Stopping at the field boundary avoids eating the rest of a log line or
            // neighbouring fields when the value is embedded in free text
            $string = (string)\preg_replace(
                \sprintf('/(%s=|\[%s\]=)([^\s&?#,;"\')\]}<>]+)/i', $quotedKey, $quotedKey),
                $replacement,
                $string
            );

            // Path style (/key/...): the value is a single path segment, so "/" terminates it
            $string = (string)\preg_replace(
                \sprintf('/(\/%s\/)([^&\/?#]+)/i', $quotedKey),
                $replacement,
                $string
            );
        }

        return $string;
    }
}

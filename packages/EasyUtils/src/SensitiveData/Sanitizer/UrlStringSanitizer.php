<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

final class UrlStringSanitizer extends AbstractStringSanitizer
{
    /**
     * The password of URL user info ("https://user:password@host"), whatever the keys to mask are.
     * A user name without a password ("tcp://name@host") is kept
     */
    private const URL_PASSWORD_PATTERN = '~\\b([a-z][a-z\\d+.-]*://[^\\s:/?#@"<>]*:)[^\\s/?#@"<>]+@~i';

    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        // Braced back-reference (${1}) so a mask pattern that starts with a digit is not merged
        // into a higher-numbered reference (e.g. $1 + "2" => $12)
        $replacement = '${1}' . $this->escapeForReplacement($maskPattern);

        $string = $this->replace(self::URL_PASSWORD_PATTERN, $replacement . '@', $string);

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
            // A separator can also be URL-encoded ("api%5Fkey" or "api+key", what http_build_query() gives
            // for "api_key" and "api key"). Possessive, so a long separator run cannot exhaust the PCRE (JIT) stack
            $quotedKey = \implode(
                '(?:[ _+-]++|%5F|%2D|%20)*+',
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
            // Brackets can be URL-encoded too ("user%5Bpassword%5D=...", what http_build_query() gives for arrays)
            $string = $this->replace(
                \sprintf(
                    '/(%s=|(?:\[|%%5B)%s(?:\]|%%5D)=)([^\s&?#,;"\')\]}<>]+)/i',
                    $quotedKey,
                    $prefixedQuotedKey
                ),
                $replacement,
                $string
            );

            // Path style (/key/...): the value is a single path segment, so "/" terminates it. Whitespace and
            // quotes end the URL, so the rest of a log line (e.g. ' HTTP/1.1" 200') is kept
            $string = $this->replace(
                \sprintf('/(\/%s\/)([^\s&\/?#"\'<>]+)/i', $prefixedQuotedKey),
                $replacement,
                $string
            );

            // Header line style ("X-Api-Key: value", as in the "headers" option of Symfony HTTP client):
            // the value is the rest of the line
            $string = $this->replace(
                \sprintf('/^(%s[ \t]*:)[ \t]*[^\r\n]+/im', $prefixedQuotedKey),
                '${1} ' . $this->escapeForReplacement($maskPattern),
                $string
            );
        }

        return $string;
    }

    /**
     * On a PCRE failure (e.g. a resource limit) the string is kept as it is instead of being lost
     */
    private function replace(string $pattern, string $replacement, string $string): string
    {
        return \preg_replace($pattern, $replacement, $string) ?? $string;
    }
}

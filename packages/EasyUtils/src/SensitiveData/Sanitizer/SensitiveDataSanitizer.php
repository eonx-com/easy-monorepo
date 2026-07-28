<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

use EonX\EasyUtils\Common\Helper\CollectorHelper;
use EonX\EasyUtils\SensitiveData\Hydrator\ObjectHydratorInterface;
use EonX\EasyUtils\SensitiveData\Transformer\ObjectTransformerInterface;

final readonly class SensitiveDataSanitizer implements SensitiveDataSanitizerInterface
{
    /**
     * @var string[]
     */
    private array $keysToMask;

    /**
     * @var \EonX\EasyUtils\SensitiveData\Transformer\ObjectTransformerInterface[]
     */
    private array $objectTransformers;

    /**
     * @var \EonX\EasyUtils\SensitiveData\Sanitizer\StringSanitizerInterface[]
     */
    private array $stringSanitizers;

    /**
     * @param string[] $keysToMask
     */
    public function __construct(
        array $keysToMask,
        private string $maskPattern,
        ?iterable $objectTransformers = null,
        ?iterable $stringSanitizers = null,
    ) {
        $this->keysToMask = \array_map(
            static fn (string $keyToMask): string => \mb_strtolower($keyToMask),
            $keysToMask
        );
        $this->objectTransformers = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($objectTransformers ?? [], ObjectTransformerInterface::class)
        );
        $this->stringSanitizers = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($stringSanitizers ?? [], StringSanitizerInterface::class)
        );
    }

    public function sanitize(mixed $data): mixed
    {
        if (\is_array($data)) {
            return $this->sanitizeArray($data);
        }

        if (\is_object($data)) {
            return $this->sanitizeObject($data);
        }

        if (\is_string($data)) {
            $decodedJson = \json_decode(
                json: $data,
                associative: true,
                flags: \JSON_BIGINT_AS_STRING
            );

            if (\is_array($decodedJson)) {
                return \json_encode(
                    $this->sanitizeArray($decodedJson),
                    \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE
                );
            }

            $decodedJson = \json_decode(
                json: \stripslashes($data),
                associative: true,
                flags: \JSON_BIGINT_AS_STRING
            );

            if (\is_array($decodedJson)) {
                return \json_encode(
                    $this->sanitizeArray($decodedJson),
                    \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE
                );
            }

            // The string is not JSON as a whole (e.g. a log message or exception text). Mask
            // secrets inside any JSON object/array embedded in it, then run the value-based
            // string sanitizers over the result
            return $this->sanitizeString($this->sanitizeEmbeddedJson($data));
        }

        return $data;
    }

    private function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = \in_array(\mb_strtolower((string)$key), $this->keysToMask, true)
                ? $this->maskPattern
                : $this->sanitize($value);
        }

        return $data;
    }

    /**
     * Masks sensitive keys inside every self-contained JSON object/array embedded in the string,
     * leaving the surrounding text untouched. Fragments are decoded with a real JSON parser (not
     * a regex), sanitized through {@see sanitizeArray()} and re-encoded in place
     */
    private function sanitizeEmbeddedJson(string $string): string
    {
        // Nothing to scan when there is no JSON object/array opener — avoids walking the whole
        // string for typical log lines
        if (\strpbrk($string, '{[') === false) {
            return $string;
        }

        $length = \strlen($string);
        $result = '';
        // Start index of the current top-level {...}/[...] span, or null while in surrounding text
        $spanStart = null;
        $depth = 0;
        $inString = false;

        // Single left-to-right pass: surrounding text is copied verbatim; a span is tracked with a
        // depth counter (string/escape aware) and handled once when it closes. This stays linear
        // even for pathological input (e.g. many unmatched "{" in a truncated payload)
        for ($index = 0; $index < $length; $index++) {
            $char = $string[$index];

            if ($spanStart === null) {
                if ($char === '{' || $char === '[') {
                    $spanStart = $index;
                    $depth = 1;
                    $inString = false;
                } else {
                    $result .= $char;
                }

                continue;
            }

            if ($inString) {
                if ($char === '\\') {
                    $index++;
                } elseif ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;
            } elseif ($char === '{' || $char === '[') {
                $depth++;
            } elseif ($char === '}' || $char === ']') {
                $depth--;

                if ($depth === 0) {
                    $result .= $this->sanitizeJsonFragment(
                        \substr($string, $spanStart, $index - $spanStart + 1)
                    );
                    $spanStart = null;
                }
            }
        }

        // An opener with no matching close (e.g. truncated JSON) — append the rest verbatim
        if ($spanStart !== null) {
            $result .= \substr($string, $spanStart);
        }

        return $result;
    }

    /**
     * Masks sensitive keys inside a single self-contained JSON fragment. Re-encodes only when
     * something was actually masked, so a valid-but-innocent fragment is kept byte-for-byte and a
     * balanced-but-invalid one (not real JSON) is returned unchanged
     */
    private function sanitizeJsonFragment(string $fragment): string
    {
        $decoded = \json_decode($fragment, true, 512, \JSON_BIGINT_AS_STRING);

        if (\is_array($decoded) === false) {
            return $fragment;
        }

        $sanitized = $this->sanitizeArray($decoded);

        return $sanitized === $decoded
            ? $fragment
            : \json_encode(
                $sanitized,
                \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE
            );
    }

    /**
     * @template T of object
     *
     * @param T $object
     *
     * @return array|T
     */
    private function sanitizeObject(object $object): array|object
    {
        foreach ($this->objectTransformers as $objectTransformer) {
            if ($objectTransformer->supports($object)) {
                $sanitizedData = $this->sanitizeArray($objectTransformer->transform($object));

                return $objectTransformer instanceof ObjectHydratorInterface
                    ? $objectTransformer->hydrate($object, $sanitizedData)
                    : $sanitizedData;
            }
        }

        return $object;
    }

    private function sanitizeString(string $string): string
    {
        foreach ($this->stringSanitizers as $stringSanitizer) {
            $string = $stringSanitizer->sanitizeString($string, $this->maskPattern, $this->keysToMask);
        }

        return $string;
    }
}

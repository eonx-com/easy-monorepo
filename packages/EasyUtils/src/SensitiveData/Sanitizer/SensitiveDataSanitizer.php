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
                return \json_encode($this->sanitizeArray($decodedJson), \JSON_THROW_ON_ERROR);
            }

            $decodedJson = \json_decode(
                json: \stripslashes($data),
                associative: true,
                flags: \JSON_BIGINT_AS_STRING
            );

            if (\is_array($decodedJson)) {
                return \json_encode($this->sanitizeArray($decodedJson), \JSON_THROW_ON_ERROR);
            }

            // The string is not JSON as a whole (e.g. a log message or exception text). Mask
            // secrets inside any JSON object/array embedded in it, then run the value-based
            // string sanitizers over the result
            return $this->sanitizeString($this->sanitizeEmbeddedJson($data));
        }

        return $data;
    }

    /**
     * Returns the index of the bracket that closes the one at $open (respecting nesting and
     * quoted strings with escapes), or null when the string is not balanced from that point
     */
    private function findMatchingBracket(string $string, int $open): ?int
    {
        $length = \strlen($string);
        $depth = 0;
        $inString = false;

        for ($index = $open; $index < $length; $index++) {
            $char = $string[$index];

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
                    return $index;
                }
            }
        }

        return null;
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
        $length = \strlen($string);
        $result = '';
        $index = 0;

        while ($index < $length) {
            $char = $string[$index];

            if ($char === '{' || $char === '[') {
                $end = $this->findMatchingBracket($string, $index);

                if ($end !== null) {
                    $fragment = \substr($string, $index, $end - $index + 1);
                    $decoded = \json_decode($fragment, true, 512, \JSON_BIGINT_AS_STRING);

                    if (\is_array($decoded)) {
                        $sanitized = $this->sanitizeArray($decoded);
                        // Re-encode only when something was actually masked; otherwise keep the
                        // original fragment verbatim so innocent JSON is not reformatted
                        $result .= $sanitized === $decoded
                            ? $fragment
                            : \json_encode($sanitized, \JSON_THROW_ON_ERROR);
                        $index = $end + 1;

                        continue;
                    }
                }
            }

            $result .= $char;
            $index++;
        }

        return $result;
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

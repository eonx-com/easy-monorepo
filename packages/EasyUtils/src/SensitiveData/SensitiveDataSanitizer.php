<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData;

use EonX\EasyUtils\Helpers\CollectorHelper;

final class SensitiveDataSanitizer implements SensitiveDataSanitizerInterface
{
    /**
     * @var string[]
     */
    private array $keysToMask;

    private string $maskPattern;

    /**
     * @var \EonX\EasyUtils\SensitiveData\ObjectTransformerInterface[]
     */
    private array $objectTransformers;

    /**
     * @var \EonX\EasyUtils\SensitiveData\StringSanitizerInterface[]
     */
    private array $stringSanitizers;

    /**
     * @param string[]|null $keysToMask
     */
    public function __construct(
        ?bool $useDefaultKeysToMask = null,
        ?array $keysToMask = null,
        ?string $maskPattern = null,
        ?iterable $objectTransformers = null,
        ?iterable $stringSanitizers = null,
    ) {
        $defaultKeysToMask = ($useDefaultKeysToMask ?? true) ? self::DEFAULT_KEYS_TO_MASK : [];
        $keysToMask = \array_map(
            static fn (string $keyToMask): string => \mb_strtolower($keyToMask),
            $keysToMask ?? []
        );

        foreach ($keysToMask as $keyToMask) {
            if (\in_array($keyToMask, $defaultKeysToMask, true) === false) {
                $defaultKeysToMask[] = $keyToMask;
            }
        }

        $this->keysToMask = $defaultKeysToMask;
        $this->maskPattern = $maskPattern ?? self::DEFAULT_MASK_PATTERN;
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
            return $this->sanitizeString($data);
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

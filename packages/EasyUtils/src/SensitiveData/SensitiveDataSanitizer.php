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

    /**
     * @var \EonX\EasyUtils\SensitiveData\ObjectTransformerInterface[]
     */
    private array $objectTransformers;

    private string $maskPattern;

    /**
     * @var \EonX\EasyUtils\SensitiveData\StringSanitizerInterface[]
     */
    private array $stringSanitizers;

    /**
     * @param string[]|null $keysToMask
     * @param iterable<mixed>|null $objectTransformers
     * @param iterable<mixed>|null $stringSanitizers
     */
    public function __construct(
        ?array $keysToMask = null,
        ?string $maskPattern = null,
        ?iterable $objectTransformers = null,
        ?iterable $stringSanitizers = null
    ) {
        $this->keysToMask = \array_map(fn (string $key) => \mb_strtolower($key), $keysToMask ?? []);
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

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = \in_array(\mb_strtolower($key), $this->keysToMask, true)
                ? $this->maskPattern
                : $this->sanitize($value);
        }

        return $data;
    }

    /**
     * @return mixed[]|object
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

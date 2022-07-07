<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Providers;

use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\ErrorHandler\Error\ClassNotFoundError;

final class ErrorCodesProvider implements ErrorCodesProviderInterface
{
    private const ERROR_CODE_NAME_PREFIX = 'ERROR_';

    /**
     * @var mixed[]
     */
    private array $groupedErrorCodes = [];

    private int $nextCategoryToUse;

    private int $categorySize;

    /**
     * @var mixed[]
     */
    private array $nextErrorCodeForCategory = [];

    /**
     * @param class-string|null $errorCodesInterface
     */
    public function __construct(private ?string $errorCodesInterface = null)
    {
    }

    public function getNextCategoryToUse(): int
    {
        return $this->nextCategoryToUse;
    }

    public function getNextErrorCodeForCategory(): array
    {
        return $this->nextErrorCodeForCategory;
    }

    public function process(): bool
    {
        foreach ($this->parseErrorCodes() as $errorCodeName => $errorCodeValue) {
            $errorCodeCategory = $errorCodeValue - ($errorCodeValue % $this->categorySize);
            $this->groupedErrorCodes[$errorCodeCategory] = $this->groupedErrorCodes[$errorCodeCategory] ?? [];
            $this->groupedErrorCodes[$errorCodeCategory][$errorCodeName] = $errorCodeValue;
        }

        if (\count($this->groupedErrorCodes) === 0) {
            return false;
        }

        \ksort($this->groupedErrorCodes);
        $this->nextCategoryToUse = (int)\max(\array_keys($this->groupedErrorCodes)) + $this->categorySize;

        foreach ($this->groupedErrorCodes as $errorCodes) {
            $this->nextErrorCodeForCategory[] = [
                'categoryName' => $this->determineCategoryName(\array_keys($errorCodes)),
                'nextErrorCodeToUse' => \max(\array_values($errorCodes)) + 1,
            ];
        }

        \usort($this->nextErrorCodeForCategory, static function (array $errorCategory1, array $errorCategory2) {
            return $errorCategory1['categoryName'] <=> $errorCategory2['categoryName'];
        });

        return true;
    }

    public function setCategorySize(int $categorySize): self
    {
        $this->categorySize = $categorySize;

        return $this;
    }

    /**
     * @param mixed[] $errorCodeNames
     */
    private function determineCategoryName(array $errorCodeNames): string
    {
        $explodedErrorCodeNames = \array_map(
            static function ($errorCodeName): array {
                return \explode('_', $errorCodeName);
            },
            $errorCodeNames
        );
        $errorCodeNamesCount = \count($explodedErrorCodeNames);
        $categoryNameParts = [];

        do {
            $errorCodeNameParts = [];

            for ($index = 0; $index < $errorCodeNamesCount; $index++) {
                $errorCodeNamePart = \array_shift($explodedErrorCodeNames[$index]);
                $errorCodeNameParts[$errorCodeNamePart] = $errorCodeNamePart;
            }

            $partIsMatched = \count($errorCodeNameParts) === 1 && \current($errorCodeNameParts) !== null;

            if ($partIsMatched) {
                $categoryNameParts[] = \current($errorCodeNameParts);
            }
        } while ($partIsMatched);

        return \implode('_', $categoryNameParts) . '_*';
    }

    /**
     * @return array<string, int>
     */
    private function parseErrorCodes(): array
    {
        if ($this->errorCodesInterface === null) {
            return [];
        }

        try {
            $reflection = new ReflectionClass($this->errorCodesInterface);
        } catch (ReflectionException $exception) {
            throw new ClassNotFoundError($exception->getMessage(), $exception);
        }

        return \array_filter(
            $reflection->getConstants(),
            static fn ($name) => \str_starts_with($name, self::ERROR_CODE_NAME_PREFIX),
            \ARRAY_FILTER_USE_KEY
        );
    }
}

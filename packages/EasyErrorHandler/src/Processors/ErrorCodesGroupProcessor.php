<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Processors;

use EonX\EasyErrorHandler\DataTransferObjects\ErrorCodeCategoryDto;
use EonX\EasyErrorHandler\DataTransferObjects\ErrorCodesDto;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesGroupProcessorInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderLocatorInterface;

final class ErrorCodesGroupProcessor implements ErrorCodesGroupProcessorInterface
{
    public function __construct(
        private readonly int $categorySize,
        private readonly string $errorCodesSource,
        private readonly ErrorCodesProviderLocatorInterface $errorCodesProviderLocator,
    ) {
    }

    public function process(): ErrorCodesDto
    {
        $groupedErrorCodes = [];

        $errorCodesProvider = $this->errorCodesProviderLocator->locate($this->errorCodesSource);

        foreach ($errorCodesProvider->provide() as $errorCodeName => $errorCodeValue) {
            $errorCodeCategory = $errorCodeValue - ($errorCodeValue % $this->categorySize);
            $groupedErrorCodes[$errorCodeCategory] = $groupedErrorCodes[$errorCodeCategory] ?? [];
            $groupedErrorCodes[$errorCodeCategory][$errorCodeName] = $errorCodeValue;
        }

        \ksort($groupedErrorCodes);

        if (\count($groupedErrorCodes) === 0) {
            return new ErrorCodesDto();
        }

        $nextGroupErrorCode = (int)\max(\array_keys($groupedErrorCodes)) + $this->categorySize;
        $nextGroupedErrorCodes = [];

        $isEnum = $this->errorCodesSource === ErrorCodesProviderLocatorInterface::SOURCE_ENUM;
        foreach ($groupedErrorCodes as $errorCodes) {
            $nextGroupedErrorCodes[] = new ErrorCodeCategoryDto(
                categoryName: $this->determineCategoryName(\array_keys($errorCodes), $isEnum),
                nextErrorCodeToUse: \max(\array_values($errorCodes)) + 1
            );
        }

        \usort(
            $nextGroupedErrorCodes,
            static fn (
                ErrorCodeCategoryDto $errorCategory1,
                ErrorCodeCategoryDto $errorCategory2
            ) => $errorCategory1->getCategoryName() <=> $errorCategory2->getCategoryName()
        );

        return new ErrorCodesDto(
            nextGroupErrorCode: $nextGroupErrorCode,
            nextGroupedErrorCodes: $nextGroupedErrorCodes
        );
    }

    /**
     * @param mixed[] $errorCodeNames
     */
    private function determineCategoryName(array $errorCodeNames, bool $isEnum): string
    {
        $explodedErrorCodeNames = \array_map(
            static function ($errorCodeName) use ($isEnum): array {
                if ($isEnum === true) {
                    $errorCodeName = \preg_replace('/([a-z])([A-Z])/u', '$1_$2', $errorCodeName);
                }
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

        return $isEnum === true
            ? \implode('', $categoryNameParts) . '*'
            : \implode('_', $categoryNameParts) . '_*';
    }
}

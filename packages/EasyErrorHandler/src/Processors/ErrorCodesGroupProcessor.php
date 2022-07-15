<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Processors;

use EonX\EasyErrorHandler\DataTransferObjects\ErrorCodeCategoryDto;
use EonX\EasyErrorHandler\DataTransferObjects\ErrorCodesDto;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesGroupProcessorInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;

final class ErrorCodesGroupProcessor implements ErrorCodesGroupProcessorInterface
{
    public function __construct(
        private int $categorySize,
        private ErrorCodesProviderInterface $errorCodesProvider
    ) {
    }

    public function process(): ErrorCodesDto
    {
        $groupedErrorCodes = [];

        foreach ($this->errorCodesProvider->provide() as $errorCodeName => $errorCodeValue) {
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

        foreach ($groupedErrorCodes as $errorCodes) {
            $nextGroupedErrorCodes[] = new ErrorCodeCategoryDto(
                categoryName: $this->determineCategoryName(\array_keys($errorCodes)),
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
}

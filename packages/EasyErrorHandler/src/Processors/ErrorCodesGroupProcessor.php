<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Processors;

use EonX\EasyErrorHandler\DataTransferObjects\ErrorCodeCategoryDto;
use EonX\EasyErrorHandler\DataTransferObjects\ErrorCodesDto;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesGroupProcessorInterface;

final class ErrorCodesGroupProcessor implements ErrorCodesGroupProcessorInterface
{
    /**
     * @param array<\EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface> $errorCodesProviders
     */
    public function __construct(
        private readonly int $categorySize,
        private readonly iterable $errorCodesProviders
    ) {
    }

    public function process(): ErrorCodesDto
    {
        $groupedErrorCodes = [];
        $providedErrorCodes = [];

        foreach ($this->errorCodesProviders as $errorCodesProvider) {
            $providedErrorCodes = $errorCodesProvider->provide();
            if (\count($providedErrorCodes) > 0) {
                break;
            }
        }

        foreach ($providedErrorCodes as $errorCodeDto) {
            $errorCodeCategory = $errorCodeDto->getErrorCode() - ($errorCodeDto->getErrorCode() % $this->categorySize);
            $groupedErrorCodes[$errorCodeCategory] = $groupedErrorCodes[$errorCodeCategory] ?? [];
            $groupedErrorCodes[$errorCodeCategory][] = $errorCodeDto;
        }

        \ksort($groupedErrorCodes);

        if (\count($groupedErrorCodes) === 0) {
            return new ErrorCodesDto();
        }

        $nextGroupErrorCode = (int)\max(\array_keys($groupedErrorCodes)) + $this->categorySize;
        $nextGroupedErrorCodes = [];

        foreach ($groupedErrorCodes as $errorCodes) {
            $nextGroupedErrorCodes[] = new ErrorCodeCategoryDto(
                categoryName: $this->determineCategoryName($errorCodes),
                nextErrorCodeToUse: $this->calculateMaxCategoryCode($errorCodes) + 1
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
     * @param array<\EonX\EasyErrorHandler\DataTransferObjects\ErrorCodeDto> $errorCodes
     */
    private function calculateMaxCategoryCode(array $errorCodes): int
    {
        $maxCode = 0;
        foreach ($errorCodes as $errorCodeDto) {
            $maxCode = \max($maxCode, $errorCodeDto->getErrorCode());
        }
        return $maxCode;
    }

    /**
     * @param array<\EonX\EasyErrorHandler\DataTransferObjects\ErrorCodeDto> $errorCodes
     */
    private function determineCategoryName(array $errorCodes): string
    {
        $explodedErrorCodeNames = [];
        $groupSeparator = $errorCodes[0]->getGroupSeparator();
        foreach ($errorCodes as $errorCodeDto) {
            $explodedErrorCodeNames[] = $errorCodeDto->getSplitName();
        }

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

        return \sprintf('%s%s*', \implode($groupSeparator, $categoryNameParts), $groupSeparator);
    }
}

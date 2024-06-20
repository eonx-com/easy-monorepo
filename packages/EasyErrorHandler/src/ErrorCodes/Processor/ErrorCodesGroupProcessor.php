<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\ErrorCodes\Processor;

use EonX\EasyErrorHandler\ErrorCodes\ValueObject\ErrorCodeCategory;
use EonX\EasyErrorHandler\ErrorCodes\ValueObject\ErrorCodes;

final class ErrorCodesGroupProcessor implements ErrorCodesGroupProcessorInterface
{
    /**
     * @param array<\EonX\EasyErrorHandler\ErrorCodes\Provider\ErrorCodesProviderInterface> $errorCodesProviders
     */
    public function __construct(
        private readonly int $categorySize,
        private readonly array $errorCodesProviders,
    ) {
    }

    public function process(): ErrorCodes
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
            $groupedErrorCodes[$errorCodeCategory] ??= [];
            $groupedErrorCodes[$errorCodeCategory][] = $errorCodeDto;
        }

        \ksort($groupedErrorCodes);

        if (\count($groupedErrorCodes) === 0) {
            return new ErrorCodes();
        }

        $nextGroupErrorCode = (int)\max(\array_keys($groupedErrorCodes)) + $this->categorySize;
        $nextGroupedErrorCodes = [];

        foreach ($groupedErrorCodes as $errorCodes) {
            $nextGroupedErrorCodes[] = new ErrorCodeCategory(
                categoryName: $this->determineCategoryName($errorCodes),
                nextErrorCodeToUse: $this->calculateMaxCategoryCode($errorCodes) + 1
            );
        }

        \usort(
            $nextGroupedErrorCodes,
            static fn (
                ErrorCodeCategory $errorCategory1,
                ErrorCodeCategory $errorCategory2,
            ): int => $errorCategory1->getCategoryName() <=> $errorCategory2->getCategoryName()
        );

        return new ErrorCodes(
            nextGroupErrorCode: $nextGroupErrorCode,
            nextGroupedErrorCodes: $nextGroupedErrorCodes
        );
    }

    /**
     * @param array<\EonX\EasyErrorHandler\ErrorCodes\ValueObject\ErrorCode> $errorCodes
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
     * @param array<\EonX\EasyErrorHandler\ErrorCodes\ValueObject\ErrorCode> $errorCodes
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

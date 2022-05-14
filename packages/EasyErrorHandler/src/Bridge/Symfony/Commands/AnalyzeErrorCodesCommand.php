<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Commands;

use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\ErrorCodes\ErrorCodesProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AnalyzeErrorCodesCommand extends Command
{
    private const DEFAULT_CATEGORY_SIZE = 100;

    /**
     * @var string
     */
    protected static $defaultName = 'easy-error-handler:error-codes:analyze';

    public function __construct(
        private ErrorCodesProviderInterface $errorCodesProvider,
        private int $categorySize = self::DEFAULT_CATEGORY_SIZE
    ) {
        parent::__construct();
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function configure(): void
    {
        $this->setDescription('Analyzes existing error codes');
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $errorCodes = $this->errorCodesProvider->provide();
        $groupedErrorCodes = [];

        foreach ($errorCodes as $errorCodeName => $errorCodeValue) {
            $errorCodeCategory = $errorCodeValue - ($errorCodeValue % $this->categorySize);
            $groupedErrorCodes[$errorCodeCategory] = $groupedErrorCodes[$errorCodeCategory] ?? [];
            $groupedErrorCodes[$errorCodeCategory][$errorCodeName] = $errorCodeValue;
        }

        if (\count($groupedErrorCodes) === 0) {
            $output->writeln('<info>No error code found.</info>');

            return self::SUCCESS;
        }

        \ksort($groupedErrorCodes);
        $nextCategoryToUse = \max(\array_keys($groupedErrorCodes)) + $this->categorySize;

        $nextErrorCodeForCategory = [];

        foreach ($groupedErrorCodes as $errorCodes) {
            $nextErrorCodeForCategory[] = [
                'categoryName' => $this->determineCategoryName(\array_keys($errorCodes)),
                'nextErrorCodeToUse' => \max(\array_values($errorCodes)) + 1,
            ];
        }

        \usort($nextErrorCodeForCategory, static function (array $errorCategory1, array $errorCategory2) {
            return $errorCategory1['categoryName'] <=> $errorCategory2['categoryName'];
        });

        $table = new Table($output);
        $table
            ->setHeaders([
                'categoryName' => 'Error code group',
                'nextErrorCodeToUse' => 'Next error code to use',
            ])
            ->setRows($nextErrorCodeForCategory);
        $table->render();

        $output->writeln(\sprintf('\n<info>The error code for the new group is %s.</info>\n', $nextCategoryToUse));

        return self::SUCCESS;
    }

    /**
     * @param string[] $errorCodeNames
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

<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Command;

use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\ErrorCodesProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AnalyzeErrorCodesCommand extends Command
{
    /**
     * @var int
     */
    private const DEFAULT_CATEGORY_SIZE = 100;

    /**
     * @var string
     */
    protected static $defaultName = 'error-codes:analyze';

    /**
     * @var int
     */
    private $categorySize;

    /**
     * @var \EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\ErrorCodesProviderInterface
     */
    private $errorCodesProvider;

    public function __construct(ErrorCodesProviderInterface $errorCodesProvider, ?int $categorySize = null)
    {
        parent::__construct();

        $this->errorCodesProvider = $errorCodesProvider;
        $this->categorySize = $categorySize ?? self::DEFAULT_CATEGORY_SIZE;
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

        \ksort($groupedErrorCodes);
        $nextCategoryToUse = \max(\array_keys($groupedErrorCodes)) + $this->categorySize;

        $nextErrorCodeForCategory = [];

        foreach ($groupedErrorCodes as $errorCodes) {
            $nextErrorCodeForCategory[] = [
                'categoryName' => $this->determineCategoryName(\array_keys($errorCodes)),
                'nextErrorCodeToUse' => \max(\array_values($errorCodes)) + 1,
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['categoryName' => 'Error code group', 'nextErrorCodeToUse' => 'Next error code to use'])
            ->setRows($nextErrorCodeForCategory);
        $table->render();

        $output->writeln("\n<info>The error code for the new group is $nextCategoryToUse.</info>\n");

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

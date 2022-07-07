<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Commands;

use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'easy-error-handler:error-codes:analyze',
    description: 'Analyzes existing error codes'
)]
final class AnalyzeErrorCodesCommand extends Command
{
    private const DEFAULT_CATEGORY_SIZE = 100;

    public function __construct(
        private ErrorCodesProviderInterface $errorCodesProvider,
        private int $categorySize = self::DEFAULT_CATEGORY_SIZE
    ) {
        parent::__construct();
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->errorCodesProvider->setCategorySize($this->categorySize)->process() === false) {
            $output->writeln('<info>No error code found.</info>');

            return self::SUCCESS;
        }

        $table = new Table($output);
        $table
            ->setHeaders([
                'categoryName' => 'Error code group',
                'nextErrorCodeToUse' => 'Next error code to use',
            ])
            ->setRows($this->errorCodesProvider->getNextErrorCodeForCategory());
        $table->render();

        $output->writeln('');
        $output->writeln(\sprintf(
            '<info>The error code for the new group is %s.</info>',
            $this->errorCodesProvider->getNextCategoryToUse()
        ));
        $output->writeln('');

        return self::SUCCESS;
    }
}

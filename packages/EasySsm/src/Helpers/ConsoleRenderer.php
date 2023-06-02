<?php

declare(strict_types=1);

namespace EonX\EasySsm\Helpers;

use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Parameters\Data\Diff;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleRenderer
{
    /**
     * @var \EonX\EasySsm\Helpers\Parameters
     */
    private $parametersHelper;

    public function __construct(Parameters $parametersHelper)
    {
        $this->parametersHelper = $parametersHelper;
    }

    public function renderDeletedParam(SsmParameter $param, OutputInterface $output): void
    {
        $output->writeln(\sprintf(
            '<fg=red>-</> %s (%s) = "%s"',
            $param->getName(),
            $param->getType(),
            $param->getValue(),
        ));
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $remote
     */
    public function renderDiff(Diff $diff, array $remote, OutputInterface $output): void
    {
        $this->renderDiffSummary($diff, $output);
        $output->writeln('');

        foreach ($diff->getNew() as $param) {
            $this->renderNewParam($param, $output);
        }

        foreach ($diff->getUpdated() as $param) {
            /** @var \EonX\EasySsm\Services\Aws\Data\SsmParameter $remoteParam */
            $remoteParam = $this->parametersHelper->findParameter($param->getName(), $remote);

            $this->renderUpdatedParam($param, $remoteParam, $output);
        }

        foreach ($diff->getDeleted() as $param) {
            $this->renderDeletedParam($param, $output);
        }
    }

    public function renderDiffSummary(Diff $diff, OutputInterface $output): void
    {
        $table = new Table($output);
        $table->setHeaderTitle('Diff Summary');
        $table->setHeaders(['New', 'Updated', 'Deleted']);
        $table->setRows([[\count($diff->getNew()), \count($diff->getUpdated()), \count($diff->getDeleted())]]);
        $table->render();
    }

    public function renderNewParam(SsmParameter $param, OutputInterface $output): void
    {
        $output->writeln(\sprintf(
            '<fg=green>+</> %s (%s) = "%s"',
            $param->getName(),
            $param->getType(),
            $param->getValue(),
        ));
    }

    public function renderUpdatedParam(SsmParameter $local, SsmParameter $remote, OutputInterface $output): void
    {
        $indent = \str_repeat(' ', 4);
        $changedType = $local->getType() !== $remote->getType();
        $changedValue = $local->getValue() !== $remote->getValue();

        $typeChange = $changedType ? \sprintf(
            ' <fg=yellow>(</>%s -> %s<fg=yellow>)</>',
            $remote->getType(),
            $local->getType(),
        ) : \sprintf(' (%s)', $local->getType());
        $noValueChange = $changedValue ? ':' : \sprintf(' = "%s"', $local->getValue());

        $output->writeln(\sprintf('<fg=yellow>~</> %s%s%s', $local->getName(), $typeChange, $noValueChange));

        if ($changedValue) {
            $output->writeln(\sprintf('%s< "%s"', $indent, $remote->getValue()));
            $output->writeln('');
            $output->writeln(\sprintf('%s> "%s"', $indent, $local->getValue()));
            $output->writeln('<fg=yellow>~</>');
        }
    }
}

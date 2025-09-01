<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Command;

use EonX\EasySecurity\Common\Configurator\SecurityContextConfiguratorInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'easy-security:configurators:list',
    description: 'List registered security context configurators with their priority ' .
    '(execution order = lower priority first).'
)]
final class ListSecurityContextConfiguratorsCommand extends Command
{
    /**
     * @param iterable<\EonX\EasySecurity\Common\Configurator\SecurityContextConfiguratorInterface> $configurators
     */
    public function __construct(
        private readonly iterable $configurators,
    ) {
        parent::__construct();
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configurators = CollectorHelper::filterByClassAsArray(
            $this->configurators,
            SecurityContextConfiguratorInterface::class
        );

        if ($configurators === []) {
            $output->writeln('<info>No security context configurator registered.</info>');

            return self::SUCCESS;
        }

        // Execution order matches FromRequestConfigurator: lower priority first
        $ordered = CollectorHelper::orderLowerPriorityFirstAsArray($configurators);

        $table = new Table($output);
        $table->setHeaders(['Order', 'Priority', 'Class']);

        $order = 1;
        foreach ($ordered as $configurator) {
            $priority = $configurator->getPriority();
            $table->addRow([$order, $priority, $configurator::class]);
            ++$order;
        }

        $output->writeln('<comment>Execution order = lower priority first. Higher priority runs last.</comment>');
        $table->render();

        return self::SUCCESS;
    }
}

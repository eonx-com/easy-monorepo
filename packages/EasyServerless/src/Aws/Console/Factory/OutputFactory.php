<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Console\Factory;

use EonX\EasyServerless\Aws\Console\Formatter\OutputMessageFormatterInterface;
use EonX\EasyServerless\Aws\Console\Output\ConsoleOutput;
use EonX\EasyServerless\Aws\Console\Output\StreamOutput;
use Symfony\Component\Console\Output\ConsoleOutput as SymfonyConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput as SymfonyStreamOutput;

final readonly class OutputFactory implements OutputFactoryInterface
{
    public function __construct(
        private ?OutputMessageFormatterInterface $messageFormatter = null,
    ) {
    }

    public function create(?OutputInterface $output = null): OutputInterface
    {
        $output ??= new SymfonyConsoleOutput();

        if ($output instanceof SymfonyConsoleOutput) {
            return ConsoleOutput::fromOutput($output, $this->messageFormatter)
                ->setOutputFactory($this);
        }

        if ($output instanceof SymfonyStreamOutput) {
            return StreamOutput::fromOutput($output, $this->messageFormatter);
        }

        return $output;
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Console\Output;

use EonX\EasyServerless\Aws\Console\Factory\OutputFactoryInterface;
use EonX\EasyServerless\Aws\Console\Formatter\OutputMessageFormatterInterface;
use EonX\EasyServerless\Aws\Console\Trait\FormattedConsoleOutputTrait;
use Symfony\Component\Console\Output\ConsoleOutput as BaseConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleOutput extends BaseConsoleOutput
{
    use FormattedConsoleOutputTrait;

    private ?OutputFactoryInterface $outputFactory = null;

    public static function fromOutput(
        BaseConsoleOutput $output,
        ?OutputMessageFormatterInterface $messageFormatter = null
    ): self {
        $self = new self($output->getVerbosity(), $output->isDecorated(), $output->getFormatter());
        $self->setMessageFormatter($messageFormatter);

        return $self;
    }

    public function getErrorOutput(): OutputInterface
    {
        $errOutput = parent::getErrorOutput();

        return $this->outputFactory?->create($errOutput) ?? $errOutput;
    }

    public function setOutputFactory(OutputFactoryInterface $outputFactory): self
    {
        $this->outputFactory = $outputFactory;

        return $this;
    }
}

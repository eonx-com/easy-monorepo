<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Console\Output;

use EonX\EasyServerless\Aws\Console\Formatter\OutputMessageFormatterInterface;
use Symfony\Component\Console\Output\StreamOutput as BaseStreamOutput;

final class StreamOutput extends BaseStreamOutput
{
    use FormattedConsoleOutputTrait;

    public static function fromOutput(
        BaseStreamOutput $output,
        ?OutputMessageFormatterInterface $messageFormatter = null,
    ): self {
        $self = new self(
            $output->getStream(),
            $output->getVerbosity(),
            $output->isDecorated(),
            $output->getFormatter()
        );

        $self->setMessageFormatter($messageFormatter);

        return $self;
    }
}

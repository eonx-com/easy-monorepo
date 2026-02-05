<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Console\Trait;

use EonX\EasyServerless\Aws\Console\Formatter\OutputMessageFormatterInterface;

trait FormattedConsoleOutputTrait
{
    private ?OutputMessageFormatterInterface $messageFormatter = null;

    public function setMessageFormatter(?OutputMessageFormatterInterface $messageFormatter = null): void
    {
        $this->messageFormatter = $messageFormatter;
    }

    protected function doWrite(string $message, bool $newline): void
    {
        parent::doWrite($this->messageFormatter?->format($message) ?? $message, $newline);
    }
}

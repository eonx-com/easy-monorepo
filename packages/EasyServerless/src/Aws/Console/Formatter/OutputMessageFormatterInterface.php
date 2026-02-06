<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Console\Formatter;

interface OutputMessageFormatterInterface
{
    public function format(string $message): string;
}

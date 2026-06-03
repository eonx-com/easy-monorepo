<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Tests\Stub\Command;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'command:invokable')]
final class InvokableCommandStub
{
    public function __invoke(): int
    {
        return 0;
    }
}

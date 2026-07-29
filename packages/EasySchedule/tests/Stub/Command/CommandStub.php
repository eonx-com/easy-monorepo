<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Tests\Stub\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'command:bar')]
final class CommandStub extends Command {}

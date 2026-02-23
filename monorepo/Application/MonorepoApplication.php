<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Application;

use EonX\EasyUtils\Common\Helper\CollectorHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

final class MonorepoApplication extends Application
{
    public const string TAG_COMMAND = 'console.command';

    /**
     * @param iterable<\Symfony\Component\Console\Command\Command> $commands
     */
    public function __construct(iterable $commands)
    {
        $this->addCommands(CollectorHelper::filterByClassAsArray($commands, Command::class));

        parent::__construct('eonx-monorepo', '1.0.0');
    }
}

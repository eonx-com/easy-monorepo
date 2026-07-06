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
        // The parent constructor must run first: Symfony Console 8 initializes internal state (typed properties)
        // during command registration, so adding commands before parent::__construct() triggers a fatal error
        parent::__construct('eonx-monorepo', '1.0.0');

        $this->addCommands(CollectorHelper::filterByClassAsArray($commands, Command::class));
    }
}

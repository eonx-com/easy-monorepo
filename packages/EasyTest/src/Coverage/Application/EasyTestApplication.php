<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Application;

use EonX\EasyUtils\Common\Helper\CollectorHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

final class EasyTestApplication extends Application
{
    public const VERSION = '1.0.0';

    /**
     * @param iterable<\Symfony\Component\Console\Command\Command> $commands
     */
    public function __construct(iterable $commands)
    {
        parent::__construct('easy-test', self::VERSION);

        $this->addCommands(CollectorHelper::filterByClassAsArray($commands, Command::class));
    }
}

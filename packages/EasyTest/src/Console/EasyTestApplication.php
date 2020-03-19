<?php

declare(strict_types=1);

namespace EonX\EasyTest\Console;

use Symfony\Component\Console\Application;

final class EasyTestApplication extends Application
{
    /**
     * @var string
     */
    public const VERSION = '1.0.0';

    /**
     * @param \Symfony\Component\Console\Command\Command[] $commands
     */
    public function __construct(array $commands)
    {
        parent::__construct('easy-test', self::VERSION);

        $this->addCommands($commands);
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyDocker\Console;

use Symfony\Component\Console\Application;
use Symplify\PackageBuilder\Console\HelpfulApplicationTrait;

final class EasyDockerApplication extends Application
{
    use HelpfulApplicationTrait;

    /** @var string */
    public const VERSION = '1.0.4';

    /**
     * EasyDockerApplication constructor.
     *
     * @param \Symfony\Component\Console\Command\Command[] $commands
     */
    public function __construct(array $commands)
    {
        parent::__construct('easy-docker', self::VERSION);

        $this->addCommands($commands);
    }
}

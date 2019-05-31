<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDocker\Console;

use Symfony\Component\Console\Application;
use Symplify\PackageBuilder\Console\HelpfulApplicationTrait;

final class EasyDockerApplication extends Application
{
    use HelpfulApplicationTrait;

    /**
     * EasyDockerApplication constructor.
     *
     * @param \Symfony\Component\Console\Command\Command[] $commands
     */
    public function __construct(array $commands)
    {
        parent::__construct('easy-docker', '1.0.1');

        $this->addCommands($commands);
    }
}

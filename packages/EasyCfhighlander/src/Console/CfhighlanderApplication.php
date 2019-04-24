<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Console;

use Symfony\Component\Console\Application;
use Symplify\PackageBuilder\Console\HelpfulApplicationTrait;

final class CfhighlanderApplication extends Application
{
    use HelpfulApplicationTrait;

    /**
     * CfhighlanderApplication constructor.
     *
     * @param \Symfony\Component\Console\Command\Command[] $commands
     */
    public function __construct(array $commands)
    {
        parent::__construct('cfhighlander', '1.0.0');

        $this->addCommands($commands);
    }
}

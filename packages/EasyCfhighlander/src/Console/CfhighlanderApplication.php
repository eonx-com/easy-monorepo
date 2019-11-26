<?php
declare(strict_types=1);

namespace EonX\EasyCfhighlander\Console;

use Symfony\Component\Console\Application;
use Symplify\PackageBuilder\Console\HelpfulApplicationTrait;

final class CfhighlanderApplication extends Application
{
    use HelpfulApplicationTrait;

    /** @var string */
    public const VERSION = '1.0.5';

    /**
     * CfhighlanderApplication constructor.
     *
     * @param \Symfony\Component\Console\Command\Command[] $commands
     */
    public function __construct(array $commands)
    {
        parent::__construct('cfhighlander', self::VERSION);

        $this->addCommands($commands);
    }
}

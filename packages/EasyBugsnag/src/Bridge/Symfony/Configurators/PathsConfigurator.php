<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Configurators;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;

final class PathsConfigurator extends AbstractClientConfigurator
{
    /**
     * @var string
     */
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;

        parent::__construct(null);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->setProjectRoot($this->projectDir . '/src');
        $bugsnag->setStripPath($this->projectDir);
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Configurators;

use Bugsnag\Client;

final class BasicsConfigurator extends AbstractClientConfigurator
{
    public function __construct(
        private string $projectRoot,
        private string $stripPath,
        private string $releaseStage,
    ) {
        parent::__construct();
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->setProjectRoot($this->projectRoot);
        $bugsnag->setReleaseStage($this->releaseStage);
        $bugsnag->setStripPath($this->stripPath);
    }
}

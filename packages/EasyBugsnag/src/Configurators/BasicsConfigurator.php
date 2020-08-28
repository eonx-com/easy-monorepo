<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Configurators;

use Bugsnag\Client;

final class BasicsConfigurator extends AbstractClientConfigurator
{
    /**
     * @var string
     */
    private $projectRoot;

    /**
     * @var string
     */
    private $releaseStage;

    /**
     * @var string
     */
    private $stripPath;

    public function __construct(string $projectRoot, string $stripPath, string $releaseStage)
    {
        $this->projectRoot = $projectRoot;
        $this->stripPath = $stripPath;
        $this->releaseStage = $releaseStage;

        parent::__construct(null);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->setProjectRoot($this->projectRoot);
        $bugsnag->setReleaseStage($this->releaseStage);
        $bugsnag->setStripPath($this->stripPath);
    }
}

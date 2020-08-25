<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Configurators;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;

final class PathsConfigurator extends AbstractClientConfigurator
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application|\Laravel\Lumen\Application
     */
    private $app;

    /**
     * @var \Illuminate\Contracts\Foundation\Application|\Laravel\Lumen\Application
     */
    public function __construct($app)
    {
        $this->app = $app;

        parent::__construct(null);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->setProjectRoot($this->app->path());
        $bugsnag->setStripPath($this->app->basePath());
    }
}

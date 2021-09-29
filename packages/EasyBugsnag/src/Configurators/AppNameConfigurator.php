<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Configurators;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Interfaces\AppNameResolverInterface;

final class AppNameConfigurator extends AbstractClientConfigurator
{
    /**
     * @var \EonX\EasyBugsnag\Interfaces\AppNameResolverInterface
     */
    private $appNameResolver;

    public function __construct(AppNameResolverInterface $appNameResolver, ?int $priority = null)
    {
        $this->appNameResolver = $appNameResolver;

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                $appName = $this->appNameResolver->resolveAppName();

                if ($appName === null) {
                    return;
                }

                $report->addMetaData([
                    'app' => [
                        'name' => $appName,
                    ],
                ]);
            }));
    }
}

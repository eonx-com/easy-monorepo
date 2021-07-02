<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Configurators;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use Symfony\Component\Filesystem\Filesystem;

final class AwsEcsFargateConfigurator extends AbstractClientConfigurator
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $storageFilename;

    /**
     * @var \Throwable
     */
    private $throwable;

    /**
     * @var string
     */
    private $url;

    public function __construct(string $storageFilename, string $url, ?int $priority = null)
    {
        $this->filesystem = new Filesystem();
        $this->storageFilename = $storageFilename;
        $this->url = $url;

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $appVersion = $this->resolveAppVersion();

        $bugsnag->setAppVersion($appVersion);

        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                $awsData = $this->getAwsFargateTaskData();

                // Something happened...
                if ($awsData === null) {
                    $message = $this->throwable !== null ? $this->throwable->getMessage() : 'Something went wrong...';

                    $report->addMetaData([
                        'aws' => [
                            'Error' => $message,
                        ],
                    ]);

                    return;
                }

                $report->addMetaData([
                    'aws' => [
                        'AvailabilityZone' => $awsData['AvailabilityZone'] ?? null,
                        'Cluster' => $awsData['Cluster'] ?? null,
                        'TaskArn' => $awsData['TaskARN'] ?? null,
                        'TaskDefinition' => \sprintf('%s:%s', $awsData['Family'] ?? null, $awsData['Revision'] ?? null),
                    ],
                ]);
            }));
    }

    private function resolveAppVersion(): ?string
    {
        $appVersion = \getenv('APP_VERSION');

        if (\is_string($appVersion)) {
            return $appVersion;
        }

        $awsData = $this->getAwsFargateTaskData();
        $image = (string)($awsData['Containers'][0]['Image'] ?? '');

        return \explode(':', $image)[1] ?? null;
    }

    /**
     * @return null|mixed[]
     */
    private function getAwsFargateTaskData(): ?array
    {
        try {
            if ($this->filesystem->exists($this->storageFilename) === false) {
                $this->filesystem->dumpFile($this->storageFilename, (string)\file_get_contents($this->url));
            }

            return \json_decode((string)\file_get_contents($this->storageFilename), true);
        } catch (\Throwable $throwable) {
            $this->throwable = $throwable;

            return null;
        }
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Configurator;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;
use UnexpectedValueException;

final class AwsEcsFargateClientConfigurator extends AbstractClientConfigurator
{
    private ?Throwable $throwable = null;

    public function __construct(
        private readonly string $storageFilename,
        private readonly ?string $url = null,
        ?int $priority = null,
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report) use ($bugsnag): void {
                $bugsnag->setAppVersion($this->resolveAppVersion());

                $awsData = $this->getAwsFargateTaskData();

                // Something happened
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

    private function getAwsFargateTaskData(): ?array
    {
        try {
            $url = $this->url ?? $this->resolveTaskDataUrl();

            if ($url === null) {
                return null;
            }

            if ($this->filesystem->exists($this->storageFilename) === false) {
                $this->filesystem->dumpFile($this->storageFilename, (string)\file_get_contents($url));
            }

            $result = \json_decode((string)\file_get_contents($this->storageFilename), true);

            if (\is_array($result) === false) {
                throw new UnexpectedValueException('Failed to decode task data.');
            }

            return $result;
        } catch (Throwable $throwable) {
            $this->throwable = $throwable;

            return null;
        }
    }

    private function resolveAppVersion(): ?string
    {
        $appVersion = \getenv('APP_VERSION');

        if (\is_string($appVersion)) {
            return $appVersion;
        }

        $awsData = $this->getAwsFargateTaskData();
        $image = (string)($awsData['Containers'][0]['Image'] ?? '');

        $appVersion = \explode(':', $image)[1] ?? null;

        if ($appVersion !== null) {
            \putenv(\sprintf('APP_VERSION=%s', $appVersion));
        }

        return $appVersion;
    }

    private function resolveTaskDataUrl(): ?string
    {
        $baseUrl = $_SERVER['ECS_CONTAINER_METADATA_URI_V4']
            ?? $_ENV['ECS_CONTAINER_METADATA_URI_V4']
            ?? \getenv('ECS_CONTAINER_METADATA_URI_V4');

        return \is_string($baseUrl) ? \sprintf('%s/task', $baseUrl) : null;
    }
}

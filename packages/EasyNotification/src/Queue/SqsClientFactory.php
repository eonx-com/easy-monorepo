<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Queue;

use Aws\Sqs\SqsClient;
use EonX\EasyNotification\Interfaces\ConfigInterface;
use EonX\EasyNotification\Interfaces\SqsClientFactoryInterface;

final class SqsClientFactory implements SqsClientFactoryInterface
{
    /**
     * @var \EonX\EasyNotification\Interfaces\ConfigInterface
     */
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function create(): SqsClient
    {
        return new SqsClient([
            'region' => $this->config->getQueueRegion(),
            'version' => 'latest',
        ]);
    }
}

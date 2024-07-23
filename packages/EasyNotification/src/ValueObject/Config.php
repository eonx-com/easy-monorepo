<?php
declare(strict_types=1);

namespace EonX\EasyNotification\ValueObject;

use EonX\EasyNotification\Helper\StringHelper;

final readonly class Config implements ConfigInterface
{
    public function __construct(
        private string $algorithm,
        private string $apiKey,
        private string $apiUrl,
        private string $providerExternalId,
        private string $queueRegion,
        private string $queueUrl,
        private string $secret,
    ) {
    }

    public static function fromArray(array $config): ConfigInterface
    {
        return new self(
            algorithm: $config['algorithm'],
            apiKey: $config['apiKey'],
            apiUrl: $config['apiUrl'],
            providerExternalId: $config['externalId'],
            queueRegion: $config['queueRegion'],
            queueUrl: $config['queueUrl'],
            secret: $config['secret']
        );
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getApiUrl(): string
    {
        return StringHelper::ensureEnd($this->apiUrl, '/');
    }

    public function getProviderExternalId(): string
    {
        return $this->providerExternalId;
    }

    public function getQueueRegion(): string
    {
        return $this->queueRegion;
    }

    public function getQueueUrl(): string
    {
        return $this->queueUrl;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }
}

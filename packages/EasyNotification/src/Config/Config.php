<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Config;

use EonX\EasyNotification\Interfaces\ConfigInterface;

use function Symfony\Component\String\u;

final class Config implements ConfigInterface
{
    /**
     * @var string
     */
    private $algorithm;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var string
     */
    private $providerExternalId;

    /**
     * @var string
     */
    private $queueRegion;

    /**
     * @var string
     */
    private $queueUrl;

    /**
     * @var string
     */
    private $secret;

    public function __construct(
        string $algorithm,
        string $apiKey,
        string $apiUrl,
        string $providerExternalId,
        string $queueRegion,
        string $queueUrl,
        string $secret
    ) {
        $this->algorithm = $algorithm;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->providerExternalId = $providerExternalId;
        $this->queueRegion = $queueRegion;
        $this->queueUrl = $queueUrl;
        $this->secret = $secret;
    }

    /**
     * @param mixed[] $config
     */
    public static function fromArray(array $config): ConfigInterface
    {
        return new static(
            $config['algorithm'],
            $config['apiKey'],
            $config['apiUrl'],
            $config['externalId'],
            $config['queueRegion'],
            $config['queueUrl'],
            $config['secret']
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
        return u($this->apiUrl)->ensureEnd('/')->toString();
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

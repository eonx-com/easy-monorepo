<?php

declare(strict_types=1);

namespace EonX\EasySsm\Factories;

use Aws\Ssm\SsmClient;
use EonX\EasySsm\Services\Aws\CredentialsProviderInterface;

final class SsmClientFactory
{
    /**
     * @var \EonX\EasySsm\Services\Aws\CredentialsProviderInterface
     */
    private $awsCredentialsProvider;

    public function __construct(CredentialsProviderInterface $awsCredentialsProvider)
    {
        $this->awsCredentialsProvider = $awsCredentialsProvider;
    }

    public function create(): SsmClient
    {
        return new SsmClient($this->awsCredentialsProvider->getCredentials());
    }
}

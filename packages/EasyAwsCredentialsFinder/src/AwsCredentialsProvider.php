<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder;

use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsFinderInterface;
use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsInterface;
use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsProviderInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

final class AwsCredentialsProvider implements AwsCredentialsProviderInterface
{
    /**
     * @var \EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsFinderInterface[]
     */
    private $finders;

    /**
     * AwsCredentialsProvider constructor.
     *
     * @param \EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsFinderInterface[] $finders
     */
    public function __construct(array $finders)
    {
        $this->finders = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($finders, AwsCredentialsFinderInterface::class)
        );
    }

    public function getCredentials(): AwsCredentialsInterface
    {
        foreach ($this->finders as $finder) {
            $credentials = $finder->findCredentials();

            if ($credentials !== null) {
                return $credentials;
            }
        }

        return new AwsCredentials();
    }
}

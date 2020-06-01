<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder;

use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsFinderInterface;
use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsInterface;
use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsProviderInterface;

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
        $this->setFinders($finders);
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

    /**
     * @param mixed[] $finders
     */
    private function setFinders(array $finders): void
    {
        $finders = \array_filter($finders, static function ($finder): bool {
            return $finder instanceof AwsCredentialsFinderInterface;
        });

        \usort($finders, function (AwsCredentialsFinderInterface $first, AwsCredentialsFinderInterface $second): int {
            return $first->getPriority() <=> $second->getPriority();
        });

        $this->finders = $finders;
    }
}

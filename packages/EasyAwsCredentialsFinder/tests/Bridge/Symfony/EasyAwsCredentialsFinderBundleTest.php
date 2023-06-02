<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Tests\Bridge\Symfony;

use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsProviderInterface;

final class EasyAwsCredentialsFinderBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $kernel = $this->getKernel();
        $container = $kernel->getContainer();

        self::assertInstanceOf(
            AwsCredentialsProviderInterface::class,
            $container->get(AwsCredentialsProviderInterface::class),
        );
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\EasyBugsnag\Configurator;

use Bugsnag\Client;
use Bugsnag\Configuration;
use Bugsnag\Report;
use EonX\EasyApiToken\Common\ValueObject\ApiKey;
use EonX\EasySecurity\Common\Context\SecurityContext;
use EonX\EasySecurity\EasyBugsnag\Configurator\SecurityContextClientConfigurator;
use EonX\EasySecurity\Tests\Stub\Resolver\SecurityContextResolverStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use ReflectionProperty;

final class SecurityContextClientConfiguratorTest extends AbstractUnitTestCase
{
    public function testItHashesTokenAndNeverExposesRawCredential(): void
    {
        $rawToken = 'super-secret-api-key';
        $context = new SecurityContext();
        $context->setToken(new ApiKey($rawToken));

        $client = new Client(new Configuration('my-api-key'));
        new SecurityContextClientConfigurator(new SecurityContextResolverStub($context))
->configure($client);

        $client->notifyError('test', 'test');

        $token = $this->getFirstReport($client)
            ->getMetaData()['security']['token'];

        self::assertSame(\hash('sha256', $rawToken), $token['original_hash']);
        self::assertArrayNotHasKey('original', $token);
        self::assertStringNotContainsString($rawToken, (string)\json_encode($token));
    }

    private function getFirstReport(Client $client): Report
    {
        $httpProperty = new ReflectionProperty($client, 'http');
        $httpClient = $httpProperty->getValue($client);
        \assert(\is_object($httpClient));

        $queueProperty = new ReflectionProperty($httpClient, 'queue');
        /** @var \Bugsnag\Report[] $reports */
        $reports = $queueProperty->getValue($httpClient);

        return $reports[0];
    }
}

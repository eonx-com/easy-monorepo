<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Bundle;

use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsCertificateAuthorityProvider;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class EasyDoctrineBundleLoggerTest extends AbstractUnitTestCase
{
    public function testAwsRdsLoggerIsDefaultLoggerWithoutMonologBundle(): void
    {
        self::bootKernel(['environment' => 'aws_rds_enable']);

        $logger = self::getPrivatePropertyValue(self::getService(AwsRdsCertificateAuthorityProvider::class), 'logger');

        self::assertInstanceOf(LoggerInterface::class, $logger);
        self::assertNotInstanceOf(Logger::class, $logger);
    }

    public function testAwsRdsLoggerUsesEasyDoctrineChannelWithMonologBundle(): void
    {
        self::bootKernel(['environment' => 'aws_rds_monolog']);

        $logger = self::getPrivatePropertyValue(self::getService(AwsRdsCertificateAuthorityProvider::class), 'logger');

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame('easy_doctrine', $logger->getName());
    }
}

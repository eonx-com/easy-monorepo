<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\AwsRds\Middleware;

use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product;
use EonX\EasyDoctrine\Tests\Fixture\App\ValueObject\Price;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;

final class MiddlewareTest extends AbstractUnitTestCase
{
    public function testItSucceeds(): void
    {
        $_SERVER['EASY_DOCTRINE_AWS_RDS_IAM_ENABLED'] = 'enabled';
        $_SERVER['EASY_DOCTRINE_AWS_RDS_SSL_ENABLED'] = 'enabled';
        self::bootKernel(['environment' => 'aws_rds_enable']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice(new Price('1000', 'USD'));
        $entityManager->persist($product);
        $entityManager->flush();

        self::assertTrue(true);
    }
}

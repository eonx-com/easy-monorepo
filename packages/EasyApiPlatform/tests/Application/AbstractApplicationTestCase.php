<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Application;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyApiPlatform\Tests\Fixture\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Traits\ArrayAssertionTrait;
use EonX\EasyTest\Traits\ContainerServiceTrait;
use EonX\EasyTest\Traits\PrivatePropertyAccessTrait;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractApplicationTestCase extends ApiTestCase
{
    use ArrayAssertionTrait;
    use ContainerServiceTrait;
    use PrivatePropertyAccessTrait;

    protected static Client $client;

    protected function setUp(): void
    {
        static::$client = static::createClient(
            [],
            [
                'headers' => ['accept' => ['application/json']],
            ]
        );
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $filesystem = new Filesystem();
        $varDir = __DIR__ . '/../Fixture/app/var';

        if ($filesystem->exists($varDir)) {
            $filesystem->remove($varDir);
        }
    }

    protected static function getKernelClass(): string
    {
        return ApplicationKernel::class;
    }

    protected function initDatabase(): void
    {
        $entityManager = self::getService(EntityManagerInterface::class);
        $metaData = $entityManager->getMetadataFactory()
            ->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metaData);
        $schemaTool->updateSchema($metaData);
    }
}

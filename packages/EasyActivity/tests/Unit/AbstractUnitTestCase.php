<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Unit;

use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyActivity\Tests\Fixture\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Traits\ContainerServiceTrait;
use EonX\EasyTest\Traits\DatabaseEntityTrait;
use EonX\EasyTest\Traits\PrivatePropertyAccessTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractUnitTestCase extends KernelTestCase
{
    use ContainerServiceTrait;
    use DatabaseEntityTrait;
    use PrivatePropertyAccessTrait;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $filesystem = new Filesystem();
        $varDir = __DIR__ . '/Bridge/Symfony/Fixtures/App/var';

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
        $entityManager = self::getEntityManager();
        $metaData = $entityManager->getMetadataFactory()
            ->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metaData);
        $schemaTool->updateSchema($metaData);
    }
}

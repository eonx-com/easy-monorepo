<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit;

use Carbon\CarbonImmutable;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyDoctrine\Tests\Fixture\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Common\Trait\ContainerServiceTrait;
use EonX\EasyTest\Common\Trait\DatabaseEntityTrait;
use EonX\EasyTest\Common\Trait\ExceptionTrait;
use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractUnitTestCase extends KernelTestCase
{
    use ContainerServiceTrait;
    use DatabaseEntityTrait;
    use ExceptionTrait;
    use PrivatePropertyAccessTrait;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $filesystem = new Filesystem();
        $varDir = __DIR__ . '/../Fixture/app/var';

        if ($filesystem->exists($varDir)) {
            $filesystem->remove($varDir);
        }

        CarbonImmutable::setTestNow();
    }

    protected static function getKernelClass(): string
    {
        return ApplicationKernel::class;
    }

    protected static function initDatabase(): void
    {
        $entityManager = self::getEntityManager();
        $metaData = $entityManager->getMetadataFactory()
            ->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metaData);
        $schemaTool->updateSchema($metaData);
    }
}

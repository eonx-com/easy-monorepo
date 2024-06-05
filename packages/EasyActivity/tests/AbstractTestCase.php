<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Traits\ContainerServiceTrait;
use EonX\EasyTest\Traits\DatabaseEntityTrait;
use EonX\EasyTest\Traits\PrivatePropertyAccessTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractTestCase extends KernelTestCase
{
    use ContainerServiceTrait;
    use DatabaseEntityTrait;
    use PrivatePropertyAccessTrait;

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

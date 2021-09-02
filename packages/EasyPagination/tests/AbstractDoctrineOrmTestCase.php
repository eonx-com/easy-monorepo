<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyPagination\Tests\Stubs\Entity\Item;
use EonX\EasyPagination\Tests\Stubs\Entity\ParentEntity;

abstract class AbstractDoctrineOrmTestCase extends AbstractTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $manager;

    protected function addItemToTable(EntityManagerInterface $manager, string $title): Item
    {
        $item = new Item();
        $item->title = $title;

        $manager->persist($item);
        $manager->flush();

        return $item;
    }

    protected function addParentToTable(EntityManagerInterface $manager, string $title, Item $item): void
    {
        $parent = new ParentEntity();
        $parent->title = $title;
        $parent->item = $item;

        $manager->persist($parent);
        $manager->flush();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     */
    protected function createItemsTable(EntityManagerInterface $manager): void
    {
        $this->createEntityTable($manager, Item::class);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     */
    protected function createParentsTable(EntityManagerInterface $manager): void
    {
        $this->createEntityTable($manager, ParentEntity::class);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        if ($this->manager !== null) {
            return $this->manager;
        }

        $conn = [
            'url' => 'sqlite:///:memory:',
        ];

        $config = new Configuration();
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        $config->setProxyDir(__DIR__);
        $config->setProxyNamespace('EasyPagination\Tests\Proxy');

        return $this->manager = EntityManager::create($conn, $config);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     */
    private function createEntityTable(EntityManagerInterface $manager, string $entity): void
    {
        $schemaTool = new SchemaTool($manager);
        $schema = $schemaTool->getSchemaFromMetadata([$manager->getClassMetadata($entity)]);

        foreach ($schema->toSql($manager->getConnection()->getDatabasePlatform()) as $sql) {
            $manager->getConnection()
                ->executeStatement($sql);
        }
    }
}

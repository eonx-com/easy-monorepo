<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyPagination\Tests\Stubs\Entity\ChildItem;
use EonX\EasyPagination\Tests\Stubs\Entity\Item;

abstract class AbstractDoctrineOrmTestCase extends AbstractTestCase
{
    private ?EntityManagerInterface $manager = null;

    protected static function addItemToTable(EntityManagerInterface $manager, string $title): Item
    {
        $item = new Item();
        $item->title = $title;

        $manager->persist($item);
        $manager->flush();

        return $item;
    }

    protected static function addParentToTable(EntityManagerInterface $manager, string $title, Item $item): void
    {
        $parent = new ChildItem();
        $parent->title = $title;
        $parent->item = $item;

        $manager->persist($parent);
        $manager->flush();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     */
    protected static function createItemsTable(EntityManagerInterface $manager): void
    {
        self::createEntityTable($manager, Item::class);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     */
    protected static function createParentsTable(EntityManagerInterface $manager): void
    {
        self::createEntityTable($manager, ChildItem::class);
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

        $this->manager = EntityManager::create($conn, $config);

        return $this->manager;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     */
    private static function createEntityTable(EntityManagerInterface $manager, string $entity): void
    {
        $schemaTool = new SchemaTool($manager);
        $schema = $schemaTool->getSchemaFromMetadata([$manager->getClassMetadata($entity)]);

        foreach ($schema->toSql($manager->getConnection()->getDatabasePlatform()) as $sql) {
            $manager->getConnection()
                ->executeStatement($sql);
        }
    }
}

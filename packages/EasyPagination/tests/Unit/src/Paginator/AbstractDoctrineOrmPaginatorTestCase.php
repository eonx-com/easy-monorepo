<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Paginator;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyPagination\Tests\Stub\Entity\ChildItem;
use EonX\EasyPagination\Tests\Stub\Entity\Item;
use EonX\EasyPagination\Tests\Unit\AbstractUnitTestCase;

abstract class AbstractDoctrineOrmPaginatorTestCase extends AbstractUnitTestCase
{
    private ?EntityManagerInterface $manager = null;

    protected static function addChildItemToTable(EntityManagerInterface $manager, string $title, Item $item): void
    {
        $childItem = new ChildItem();
        $childItem->setTitle($title);
        $childItem->setItem($item);

        $manager->persist($childItem);
        $manager->flush();
    }

    protected static function addItemToTable(EntityManagerInterface $manager, string $title): Item
    {
        $item = new Item();
        $item->setTitle($title);

        $manager->persist($item);
        $manager->flush();

        return $item;
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
        $config->setMetadataDriverImpl(new AttributeDriver([]));
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

<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Paginator;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyPagination\Tests\Stub\Entity\ChildItem;
use EonX\EasyPagination\Tests\Stub\Entity\Item;
use EonX\EasyPagination\Tests\Stub\Enum\Status;
use EonX\EasyPagination\Tests\Stub\Type\SqliteStringUuidType;
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

    protected static function addItemToTable(
        EntityManagerInterface $manager,
        string $title,
        ?Status $status = null,
    ): Item {
        $item = new Item();
        $item->setTitle($title);
        $item->setStatus($status);

        $manager->persist($item);
        $manager->flush();

        return $item;
    }

    protected static function createItemsTable(EntityManagerInterface $manager): void
    {
        self::createEntityTable($manager, Item::class);
    }

    protected static function createParentsTable(EntityManagerInterface $manager): void
    {
        self::createEntityTable($manager, ChildItem::class);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        if ($this->manager !== null) {
            return $this->manager;
        }

        $manager = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $config = new Configuration();
        $config->setMetadataDriverImpl(new AttributeDriver([]));
        $config->setProxyDir(__DIR__);
        $config->setProxyNamespace('EasyPagination\Tests\Proxy');

        if (Type::hasType(SqliteStringUuidType::NAME) === false) {
            Type::addType(SqliteStringUuidType::NAME, SqliteStringUuidType::class);
        }

        $this->manager = new EntityManager($manager, $config);

        return $this->manager;
    }

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

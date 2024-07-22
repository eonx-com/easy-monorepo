<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Listener;

use Carbon\CarbonImmutable;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use EonX\EasyDoctrine\Common\Entity\TimestampableInterface;
use EonX\EasyDoctrine\Common\Entity\TimestampableTrait;
use EonX\EasyDoctrine\Common\Listener\TimestampableListener;
use EonX\EasyDoctrine\Tests\Fixture\Entity\Product;
use EonX\EasyDoctrine\Tests\Stub\EntityManager\EntityManagerStub;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use stdClass;

#[CoversClass(TimestampableListener::class)]
final class TimestampableListenerTest extends AbstractUnitTestCase
{
    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function testLoadClassMetadataDoesNothingWhenClassIsNotFullyBuilt(): void
    {
        $classMetadata = new ClassMetadata(Product::class);
        $metadataEventArgs = new LoadClassMetadataEventArgs(
            $classMetadata,
            EntityManagerStub::createFromEventManager()
        );
        $listener = new TimestampableListener();

        $listener->loadClassMetadata($metadataEventArgs);

        self::assertCount(0, $classMetadata->getLifecycleCallbacks(Events::prePersist));
        self::assertCount(0, $classMetadata->getLifecycleCallbacks(Events::preUpdate));
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function testLoadClassMetadataDoesNothingWhenEntityIsMappedSuperClass(): void
    {
        $entity = new class() implements TimestampableInterface {
            use TimestampableTrait;
        };
        $classMetadata = new ClassMetadata(Product::class);
        $classMetadata->reflClass = new ReflectionClass($entity);
        $classMetadata->isMappedSuperclass = true;
        $metadataEventArgs = new LoadClassMetadataEventArgs(
            $classMetadata,
            EntityManagerStub::createFromEventManager()
        );
        $listener = new TimestampableListener();

        $listener->loadClassMetadata($metadataEventArgs);

        self::assertCount(0, $classMetadata->getLifecycleCallbacks(Events::prePersist));
        self::assertCount(0, $classMetadata->getLifecycleCallbacks(Events::preUpdate));
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function testLoadClassMetadataDoesNothingWhenEntityIsNotTimestampable(): void
    {
        $classMetadata = new ClassMetadata(Product::class);
        $classMetadata->reflClass = new ReflectionClass(new stdClass());
        $metadataEventArgs = new LoadClassMetadataEventArgs(
            $classMetadata,
            EntityManagerStub::createFromEventManager()
        );
        $listener = new TimestampableListener();

        $listener->loadClassMetadata($metadataEventArgs);

        self::assertCount(0, $classMetadata->getLifecycleCallbacks(Events::prePersist));
        self::assertCount(0, $classMetadata->getLifecycleCallbacks(Events::preUpdate));
    }

    /**
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function testLoadClassMetadataSucceeds(): void
    {
        $entity = new class() implements TimestampableInterface {
            use TimestampableTrait;
        };
        $classMetadata = new ClassMetadata(Product::class);
        $classMetadata->reflClass = new ReflectionClass($entity);
        $metadataEventArgs = new LoadClassMetadataEventArgs(
            $classMetadata,
            EntityManagerStub::createFromEventManager()
        );
        $listener = new TimestampableListener();

        $listener->loadClassMetadata($metadataEventArgs);

        self::assertCount(1, $classMetadata->getLifecycleCallbacks(Events::prePersist));
        self::assertCount(1, $classMetadata->getLifecycleCallbacks(Events::preUpdate));
        self::assertSame(CarbonImmutable::class, $classMetadata->getFieldMapping('createdAt')['type']);
        self::assertSame(CarbonImmutable::class, $classMetadata->getFieldMapping('updatedAt')['type']);
    }
}

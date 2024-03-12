<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Listeners;

use Carbon\CarbonImmutable;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use EonX\EasyDoctrine\Interfaces\TimestampableInterface;
use EonX\EasyDoctrine\Listeners\TimestampableEventListener;
use EonX\EasyDoctrine\Tests\AbstractTestCase;
use EonX\EasyDoctrine\Tests\Fixtures\Product;
use EonX\EasyDoctrine\Tests\Stubs\EntityManagerStub;
use EonX\EasyDoctrine\Traits\TimestampableTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use stdClass;

#[CoversClass(TimestampableEventListener::class)]
final class TimestampableEventListenerTest extends AbstractTestCase
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
        $subscriber = new TimestampableEventListener();

        $subscriber->loadClassMetadata($metadataEventArgs);

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
        $subscriber = new TimestampableEventListener();

        $subscriber->loadClassMetadata($metadataEventArgs);

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
        $subscriber = new TimestampableEventListener();

        $subscriber->loadClassMetadata($metadataEventArgs);

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
        $subscriber = new TimestampableEventListener();

        $subscriber->loadClassMetadata($metadataEventArgs);

        self::assertCount(1, $classMetadata->getLifecycleCallbacks(Events::prePersist));
        self::assertCount(1, $classMetadata->getLifecycleCallbacks(Events::preUpdate));
        self::assertSame(CarbonImmutable::class, $classMetadata->getFieldMapping('createdAt')['type']);
        self::assertSame(CarbonImmutable::class, $classMetadata->getFieldMapping('updatedAt')['type']);
    }
}

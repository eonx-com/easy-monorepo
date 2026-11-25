<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\SnapStart\Warmer;

use ArrayObject;
use Composer\Autoload\ClassLoader;
use EonX\EasyServerless\SnapStart\Warmer\ClassMapWarmer;
use EonX\EasyServerless\Tests\Fixture\ClassMap\IncludedClassMap;
use EonX\EasyServerless\Tests\Fixture\ClassMap\SignatureClassMap;
use EonX\EasyServerless\Tests\Stub\SnapStart\Hook\AfterRestoreHookStub;
use EonX\EasyServerless\Tests\Stub\SnapStart\Warmer\SnapStartWarmerStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Error;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use RuntimeException;

final class ClassMapWarmerTest extends AbstractUnitTestCase
{
    private ?ClassLoader $classLoader = null;

    protected function tearDown(): void
    {
        $this->classLoader?->unregister();

        parent::tearDown();
    }

    #[RunInSeparateProcess]
    public function testWarmIgnoresClassThatCannotBeLoaded(): void
    {
        $errors = new ArrayObject();
        $missingFileClass = 'EonX\EasyServerless\Tests\Fixture\ClassMap\MissingFileClassMap';
        $this->classLoader = new ClassLoader(__DIR__);
        $this->classLoader->addClassMap([
            $missingFileClass => __DIR__ . '/../../../../Fixture/ClassMap/MissingFileClassMap.php',
            IncludedClassMap::class => __DIR__ . '/../../../../Fixture/ClassMap/IncludedClassMap.php',
        ]);
        $this->classLoader->register();
        $sut = new ClassMapWarmer(['EonX\EasyServerless\Tests\Fixture\ClassMap\\']);
        \set_error_handler(static function (int $type, string $message) use ($errors): bool {
            $errors->append($message);

            return true;
        });

        try {
            $sut->warm();
        } finally {
            \restore_error_handler();
        }

        self::assertSame([], $errors->getArrayCopy());
        self::assertFalse(\class_exists($missingFileClass, false));
        self::assertTrue(\class_exists(IncludedClassMap::class, false));
    }

    #[RunInSeparateProcess]
    public function testWarmIgnoresClassWhoseLoadingThrows(): void
    {
        $unloadableClass = 'EonX\EasyServerless\Tests\Fixture\ClassMap\UnloadableClassMap';
        $this->classLoader = new ClassLoader(__DIR__);
        $this->classLoader->addClassMap([
            $unloadableClass => __DIR__ . '/../../../../Fixture/ClassMap/UnloadableClassMap.php',
            IncludedClassMap::class => __DIR__ . '/../../../../Fixture/ClassMap/IncludedClassMap.php',
        ]);
        $this->classLoader->register();
        // As PHP does when the parent class does not exist, e.g. when it is only installed in dev
        \spl_autoload_register(
            callback: static function (string $class) use ($unloadableClass): void {
                if ($class === $unloadableClass) {
                    throw new Error('Class "Acme\Dev\ParentClass" not found');
                }
            },
            prepend: true
        );
        $sut = new ClassMapWarmer(['EonX\EasyServerless\Tests\Fixture\ClassMap\\']);

        $sut->warm();

        self::assertFalse(\class_exists($unloadableClass, false));
        self::assertTrue(\class_exists(IncludedClassMap::class, false));
    }

    #[RunInSeparateProcess]
    public function testWarmLoadsClassesOfNamespaces(): void
    {
        $this->classLoader = new ClassLoader(__DIR__);
        $this->classLoader->addClassMap([
            IncludedClassMap::class => __DIR__ . '/../../../../Fixture/ClassMap/IncludedClassMap.php',
            SnapStartWarmerStub::class => __DIR__ . '/../../../../Stub/SnapStart/Warmer/SnapStartWarmerStub.php',
        ]);
        $this->classLoader->register();
        $sut = new ClassMapWarmer(['EonX\EasyServerless\Tests\Fixture\ClassMap\\']);

        $sut->warm();

        self::assertTrue(\class_exists(IncludedClassMap::class, false));
        self::assertFalse(\class_exists(SnapStartWarmerStub::class, false));
    }

    #[RunInSeparateProcess]
    public function testWarmLoadsClassesUsedInSignaturesOfLoadedClasses(): void
    {
        $this->classLoader = new ClassLoader(__DIR__);
        $this->classLoader->addClassMap([
            SignatureClassMap::class => __DIR__ . '/../../../../Fixture/ClassMap/SignatureClassMap.php',
        ]);
        $this->classLoader->register();
        $sut = new ClassMapWarmer(['EonX\EasyServerless\Tests\Fixture\ClassMap\\']);

        $sut->warm();

        self::assertTrue(\class_exists(SignatureClassMap::class, false));
        self::assertTrue(\class_exists(AfterRestoreHookStub::class, false));
    }

    public function testWarmThrowsWhenNoClassOfNamespacesIsInClassMap(): void
    {
        $this->classLoader = new ClassLoader(__DIR__);
        $this->classLoader->addClassMap([
            IncludedClassMap::class => __DIR__ . '/../../../../Fixture/ClassMap/IncludedClassMap.php',
        ]);
        $this->classLoader->register();
        $sut = new ClassMapWarmer(['App\\', 'Acme\\']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No class of the namespaces "App\", "Acme\" in the Composer class map, dump the'
            . ' autoloader with --optimize or --classmap-authoritative.');

        $sut->warm();
    }
}

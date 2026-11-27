<?php
declare(strict_types=1);

namespace EonX\EasyServerless\SnapStart\Warmer;

use Composer\Autoload\ClassLoader;
use RuntimeException;
use Symfony\Component\DependencyInjection\Dumper\Preloader;
use Throwable;

/**
 * Loads the classes of the namespaces from the Composer class map, so they are compiled before the snapshot. The class
 * map only lists the application classes when the autoloader is optimized (--optimize or --classmap-authoritative).
 */
final readonly class ClassMapWarmer implements SnapStartWarmerInterface
{
    /**
     * @param string[] $namespaces
     */
    public function __construct(
        private array $namespaces = ['App\\'],
    ) {
    }

    public function warm(): void
    {
        $classes = [];

        foreach (ClassLoader::getRegisteredLoaders() as $classLoader) {
            foreach (\array_keys($classLoader->getClassMap()) as $class) {
                if ($this->isInNamespaces($class)) {
                    $classes[$class] = true;
                }
            }
        }

        $classes = \array_keys($classes);

        if ($classes === []) {
            throw new RuntimeException(\sprintf(
                'No class of the namespaces "%s" in the Composer class map, dump the autoloader with --optimize'
                . ' or --classmap-authoritative.',
                \implode('", "', $this->namespaces)
            ));
        }

        // Ignores the warnings, e.g. of a class map entry whose file does not exist
        \set_error_handler(static fn (): bool => true);

        try {
            foreach ($classes as $class) {
                try {
                    \class_exists($class);
                } catch (Throwable) {
                    // The class cannot be loaded, e.g. its parent class is only installed in dev
                }
            }
        } finally {
            \restore_error_handler();
        }

        // Also loads the classes used in the signatures of the loaded classes
        if (\class_exists(Preloader::class)) {
            Preloader::preload($classes);
        }
    }

    private function isInNamespaces(string $class): bool
    {
        foreach ($this->namespaces as $namespace) {
            if (\str_starts_with($class, $namespace)) {
                return true;
            }
        }

        return false;
    }
}

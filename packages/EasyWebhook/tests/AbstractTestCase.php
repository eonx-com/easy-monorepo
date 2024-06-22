<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyRandom\Generator\SymfonyUuidV6Generator;
use EonX\EasyWebhook\Interfaces\Stores\DataCleanerInterface;
use EonX\EasyWebhook\Stores\NullDataCleaner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    private static ?RandomGeneratorInterface $randomGenerator = null;

    private ?DataCleanerInterface $dataCleaner = null;

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    protected static function getRandomGenerator(): RandomGeneratorInterface
    {
        self::$randomGenerator ??= new RandomGenerator(new SymfonyUuidV6Generator());

        return self::$randomGenerator;
    }

    protected function getDataCleaner(): DataCleanerInterface
    {
        $this->dataCleaner ??= new NullDataCleaner();

        return $this->dataCleaner;
    }
}

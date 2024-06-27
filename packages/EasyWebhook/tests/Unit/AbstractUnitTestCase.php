<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit;

use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV6Generator;
use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface;
use EonX\EasyWebhook\Common\Cleaner\NullDataCleaner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractUnitTestCase extends TestCase
{
    private static ?RandomGeneratorInterface $randomGenerator = null;

    private ?DataCleanerInterface $dataCleaner = null;

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../../var';

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

<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit;

use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyRandom\Generator\UuidGenerator;
use EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface;
use EonX\EasyWebhook\Common\Cleaner\NullDataCleaner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Factory\UuidFactory;

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
        $filesystem = new Filesystem();
        $var = __DIR__ . '/../../var';

        if ($filesystem->exists($var)) {
            $filesystem->remove($var);
        }

        parent::tearDown();
    }

    protected static function getRandomGenerator(): RandomGeneratorInterface
    {
        self::$randomGenerator ??= new RandomGenerator(new UuidGenerator(new UuidFactory()));

        return self::$randomGenerator;
    }

    protected function getDataCleaner(): DataCleanerInterface
    {
        $this->dataCleaner ??= new NullDataCleaner();

        return $this->dataCleaner;
    }
}

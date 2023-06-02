<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
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
    /**
     * @var \EonX\EasyWebhook\Interfaces\Stores\DataCleanerInterface|null
     */
    private $dataCleaner = null;

    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface|null
     */
    private $random = null;

    protected function getDataCleaner(): DataCleanerInterface
    {
        return $this->dataCleaner = $this->dataCleaner ?? new NullDataCleaner();
    }

    protected function getRandomGenerator(): RandomGeneratorInterface
    {
        return $this->random = $this->random ?? (new RandomGenerator())->setUuidV4Generator(
            new RamseyUuidV4Generator()
        );
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }
}

<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractUnitTestCase extends TestCase
{
    /**
     * @var string[]
     */
    protected static array $defaultConfig = [
        'algorithm' => 'sha256',
        'apiKey' => 'my-api-key',
        'apiUrl' => 'http://eonx.com/',
        'externalId' => 'my-provider',
        'queueRegion' => 'ap-southeast-2',
        'queueUrl' => 'https://sqs.my-queue',
        'secret' => 'my-secret',
    ];

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $filesystem = new Filesystem();
        $var = __DIR__ . '/../../var';

        if ($filesystem->exists($var)) {
            $filesystem->remove($var);
        }
    }
}

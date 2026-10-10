<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Application;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyApiPlatform\Tests\Fixture\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Common\Trait\ArrayAssertionTrait;
use EonX\EasyTest\Common\Trait\ContainerServiceTrait;
use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uses Symfony KernelBrowser instead of API Platform test client, as the latter lives in the separate
 * api-platform/test package since API Platform 5.0, which has no 4.x versions.
 */
abstract class AbstractApplicationTestCase extends WebTestCase
{
    use ArrayAssertionTrait;
    use ContainerServiceTrait;
    use PrivatePropertyAccessTrait;

    protected static KernelBrowser $client;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $filesystem = new Filesystem();
        $varDir = __DIR__ . '/../Fixture/app/var';

        if ($filesystem->exists($varDir)) {
            $filesystem->remove($varDir);
        }
    }

    protected function setUp(): void
    {
        self::setUpClient();
    }

    protected static function assertArraySubset(array $subset, array $array): void
    {
        self::assertEquals(\array_replace_recursive($array, $subset), $array);
    }

    protected static function getKernelClass(): string
    {
        return ApplicationKernel::class;
    }

    protected static function getResponseData(Response $response): array
    {
        return (array)\json_decode((string)$response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }

    /**
     * @param array{headers?: array<string, string>, json?: array, body?: string|null} $options
     */
    protected static function request(string $method, string $url, ?array $options = null): Response
    {
        $headers = \array_change_key_case(['accept' => 'application/json', ...$options['headers'] ?? []]);
        $content = $options['body'] ?? null;

        if (isset($options['json'])) {
            $headers['content-type'] ??= 'application/json';
            $content = \json_encode($options['json'], \JSON_THROW_ON_ERROR);
        }

        $server = [];
        foreach ($headers as $name => $value) {
            $name = \strtoupper(\str_replace('-', '_', $name));
            $server[$name === 'CONTENT_TYPE' ? $name : 'HTTP_' . $name] = $value;
        }

        self::$client->request($method, $url, server: $server, content: $content);

        return self::$client->getResponse();
    }

    protected static function setUpClient(?array $kernelOptions = null): void
    {
        self::ensureKernelShutdown();
        self::$client = self::createClient($kernelOptions ?? []);
        self::$client->followRedirects(false);
    }

    protected function initDatabase(): void
    {
        $entityManager = self::getService(EntityManagerInterface::class);
        $metaData = $entityManager->getMetadataFactory()
            ->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metaData);
        $schemaTool->updateSchema($metaData);
    }
}

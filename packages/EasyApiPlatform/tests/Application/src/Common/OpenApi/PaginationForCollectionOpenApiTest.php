<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Application\Common\OpenApi;

use Composer\InstalledVersions;
use EonX\EasyApiPlatform\Tests\Application\AbstractApplicationTestCase;

final class PaginationForCollectionOpenApiTest extends AbstractApplicationTestCase
{
    public function testWithCustomPaginator(): void
    {
        $filename = __DIR__ . '/../../../../Fixture/OpenApi/v'
            . self::getApiPlatformVersion() . '/with_custom_pagination.json';

        $result = self::generateOpenApiJson();
        echo $result;
        self::assertStringEqualsFile($filename, $result . "\n");
    }

    public function testWithDefaultPaginator(): void
    {
        self::setUpClient(['environment' => 'default_paginator']);
        $filename = __DIR__ . '/../../../../Fixture/OpenApi/v'
            . self::getApiPlatformVersion() . '/with_default_pagination.json';

        $result = self::generateOpenApiJson();
        echo $result;
        self::assertStringEqualsFile($filename, $result . "\n");
    }

    private static function generateOpenApiJson(): string
    {
        /** @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface $normalizer */
        $normalizer = self::getService('api_platform.serializer');
        /** @var \ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface $factory */
        $factory = self::getService('api_platform.openapi.factory');

        $data = $normalizer->normalize($factory(), 'json', ['spec_version' => '3']);

        return \json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR);
    }

    private static function getApiPlatformVersion(): int
    {
        if (\class_exists(InstalledVersions::class)) {
            $installedVersion = InstalledVersions::getVersion('api-platform/core');

            if ($installedVersion !== null && \version_compare($installedVersion, '4', '>=')) {
                return 4;
            }
        }

        return 3;
    }
}

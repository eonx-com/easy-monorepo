<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Application\Common\OpenApi;

use Composer\InstalledVersions;
use EonX\EasyApiPlatform\Tests\Application\AbstractApplicationTestCase;

final class PaginationForCollectionOpenApiTest extends AbstractApplicationTestCase
{
    /**
     * API Platform documents the minimum of the page parameter since 5.0.6,
     * see https://github.com/api-platform/core/pull/8812
     */
    private const string PAGE_PARAMETER_MINIMUM = ",\n                            \"minimum\": 1";

    public function testWithCustomPaginator(): void
    {
        $filename = __DIR__ . '/../../../../Fixture/OpenApi/with_custom_pagination.json';

        $result = self::generateOpenApiJson();
        self::assertSame(self::getExpectedOpenApiJson($filename), $result . "\n");
    }

    public function testWithDefaultPaginator(): void
    {
        self::setUpClient(['environment' => 'default_paginator']);
        $filename = __DIR__ . '/../../../../Fixture/OpenApi/with_default_pagination.json';

        $result = self::generateOpenApiJson();
        self::assertSame(self::getExpectedOpenApiJson($filename), $result . "\n");
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

    private static function getExpectedOpenApiJson(string $filename): string
    {
        $expected = (string)\file_get_contents($filename);
        $installedVersion = (string)InstalledVersions::getVersion('api-platform/openapi');

        if (\version_compare($installedVersion, '5.0.6', '<')) {
            return \str_replace(self::PAGE_PARAMETER_MINIMUM, '', $expected);
        }

        return $expected;
    }
}

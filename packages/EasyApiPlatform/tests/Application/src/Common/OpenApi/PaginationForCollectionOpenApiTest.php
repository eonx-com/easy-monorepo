<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Application\Common\OpenApi;

use EonX\EasyApiPlatform\Tests\Application\AbstractApplicationTestCase;

final class PaginationForCollectionOpenApiTest extends AbstractApplicationTestCase
{
    public function testWithCustomPaginator(): void
    {
        $result = self::generateOpenApiJson();

        self::assertStringEqualsFile(
            __DIR__ . '/../../../../Fixture/OpenApi/with_custom_pagination.json',
            $result
        );
    }

    public function testWithDefaultPaginator(): void
    {
        self::setUpClient(['environment' => 'default_paginator']);

        $result = self::generateOpenApiJson();

        self::assertStringEqualsFile(
            __DIR__ . '/../../../../Fixture/OpenApi/with_default_pagination.json',
            $result
        );
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
}

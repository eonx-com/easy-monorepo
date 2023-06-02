<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Routing;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use EonX\EasyApiPlatform\Routing\IriConverter;
use EonX\EasyApiPlatform\Routing\NoIriItemInterface;
use EonX\EasyApiPlatform\Routing\SelfProvidedIriItemInterface;
use EonX\EasyApiPlatform\Tests\AbstractTestCase;
use Mockery\MockInterface;

final class IriConverterTest extends AbstractTestCase
{
    public function testGetIriFromResourceSucceedsWhenResourceImplementsNoIriItemInterface(): void
    {
        $resource = new class() implements NoIriItemInterface {
            // Just an object
        };
        /** @var \ApiPlatform\Api\IriConverterInterface $decoratedIriConverter */
        $decoratedIriConverter = $this->mock(IriConverterInterface::class);
        $iriConverter = new IriConverter($decoratedIriConverter);

        $result = $iriConverter->getIriFromResource($resource);

        self::assertSame('__iri_not_supported', $result);
    }

    public function testGetIriFromResourceSucceedsWhenResourceImplementsSelfProvidedIriItemInterface(): void
    {
        $resource = new class() implements SelfProvidedIriItemInterface {
            public function getIri(): string
            {
                return 'some-iri';
            }
        };
        /** @var \ApiPlatform\Api\IriConverterInterface $decoratedIriConverter */
        $decoratedIriConverter = $this->mock(IriConverterInterface::class);
        $iriConverter = new IriConverter($decoratedIriConverter);

        $result = $iriConverter->getIriFromResource($resource);

        self::assertSame('some-iri', $result);
    }

    public function testGetIriFromResourceSucceedsWhenResourceWithoutAnyInterface(): void
    {
        $resource = new class() {
            // Just an object
        };
        /** @var \ApiPlatform\Api\IriConverterInterface $decoratedIriConverter */
        $decoratedIriConverter = $this->mock(
            IriConverterInterface::class,
            static function (MockInterface $mock) use ($resource): void {
                $mock->expects('getIriFromResource')
                    ->with($resource, UrlGeneratorInterface::ABS_PATH, null, [])
                    ->andReturn('some-iri');
            }
        );
        $iriConverter = new IriConverter($decoratedIriConverter);

        $result = $iriConverter->getIriFromResource($resource);

        self::assertSame('some-iri', $result);
    }

    public function testGetResourceFromIriSucceeds(): void
    {
        /** @var \ApiPlatform\Api\IriConverterInterface $decoratedIriConverter */
        $decoratedIriConverter = $this->mock(IriConverterInterface::class, static function (MockInterface $mock): void {
            $mock->expects('getResourceFromIri')
                ->with('some-iri', [], null)
                ->andReturnNull();
        });
        $iriConverter = new IriConverter($decoratedIriConverter);

        $result = $iriConverter->getResourceFromIri('some-iri');

        self::assertNull($result);
    }
}

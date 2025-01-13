<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Unit\Common\Filter;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\IriConverterInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyApiPlatform\Common\Filter\AdvancedSearchFilter;
use EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\ApiResource\UuidIdentifierDummy;
use EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\NameConverter\CustomNameConverter;
use Prophecy\Argument;
use Prophecy\Prophet;

final class AdvancedSearchFilterWithUuidTest extends AbstractFilterTestCase
{
    protected string $filterClass = AdvancedSearchFilter::class;

    protected string $resourceClass = UuidIdentifierDummy::class;

    public static function provideApplyTestData(): array
    {
        $filterFactory = self::buildAdvancedSearchFilter(...);
        $validUuid = '9584fbef-e849-41e3-912b-f2c509874a70';

        return [
            'invalid uuid for id' => [
                [
                    'id' => 'exact',
                ],
                [
                    'id' => 'some-invalid-uuid',
                ],
                'SELECT o FROM ' . UuidIdentifierDummy::class . ' o WHERE o.id = :id_p1',
                [
                    'id_p1' => '00000000-0000-0000-0000-000000000000',
                ],
                $filterFactory,
            ],

            'valid uuid for id' => [
                [
                    'id' => 'exact',
                ],
                [
                    'id' => $validUuid,
                ],
                'SELECT o FROM ' . UuidIdentifierDummy::class . ' o WHERE o.id = :id_p1',
                ['id_p1' => $validUuid],
                $filterFactory,
            ],

            'invalid uuid for uuidField' => [
                [
                    'uuidField' => 'exact',
                ],
                [
                    'uuidField' => 'some-invalid-uuid',
                ],
                'SELECT o FROM ' . UuidIdentifierDummy::class . ' o',
                [],
                $filterFactory,
            ],

            'valid uuid for uuidField' => [
                [
                    'uuidField' => 'exact',
                ],
                [
                    'uuidField' => $validUuid,
                ],
                'SELECT o FROM ' . UuidIdentifierDummy::class . ' o WHERE o.uuidField = :uuidField_p1',
                ['uuidField_p1' => $validUuid],
                $filterFactory,
            ],

            'invalid uuid for relatedUuidIdentifierDummy' => [
                [
                    'relatedUuidIdentifierDummy' => 'exact',
                ],
                [
                    'relatedUuidIdentifierDummy' => 'some-invalid-uuid',
                ],
                'SELECT o FROM ' . UuidIdentifierDummy::class . ' o' .
                ' WHERE o.relatedUuidIdentifierDummy = :relatedUuidIdentifierDummy_p1',
                [
                    'relatedUuidIdentifierDummy_p1' => '00000000-0000-0000-0000-000000000000',
                ],
                $filterFactory,
            ],

            'valid uuid for relatedUuidIdentifierDummy' => [
                [
                    'relatedUuidIdentifierDummy' => 'exact',
                ],
                [
                    'relatedUuidIdentifierDummy' => $validUuid,
                ],
                'SELECT o FROM ' . UuidIdentifierDummy::class . ' o' .
                ' WHERE o.relatedUuidIdentifierDummy = :relatedUuidIdentifierDummy_p1',
                ['relatedUuidIdentifierDummy_p1' => $validUuid],
                $filterFactory,
            ],
        ];
    }

    protected static function buildAdvancedSearchFilter(
        ManagerRegistry $managerRegistry,
        ?array $properties = null,
    ): AdvancedSearchFilter {
        $prophet = new Prophet();
        $iriConverterProphecy = $prophet->prophesize(IriConverterInterface::class);

        $iriConverterProphecy->getResourceFromIri(Argument::type('string'), ['fetch_data' => false])->will(function (
        ): void {
            throw new InvalidArgumentException();
        });

        /** @var \ApiPlatform\Metadata\IriConverterInterface $iriConverter */
        $iriConverter = $iriConverterProphecy->reveal();
        $propertyAccessor = self::$kernel?->getContainer()->get('test.property_accessor');

        return new AdvancedSearchFilter(
            $managerRegistry,
            $iriConverter,
            $propertyAccessor,
            null,
            $properties,
            null,
            new CustomNameConverter(),
        );
    }
}

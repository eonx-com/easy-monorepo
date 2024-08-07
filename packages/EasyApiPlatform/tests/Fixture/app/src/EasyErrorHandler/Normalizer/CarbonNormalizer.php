<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\Normalizer;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class CarbonNormalizer implements DenormalizerInterface
{
    /**
     * @param string $data
     */
    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        ?array $context = null,
    ): CarbonImmutable {
        if (Carbon::canBeCreatedFromFormat($data, 'Y-m-d')
            || Carbon::canBeCreatedFromFormat($data, Carbon::RFC3339)
        ) {
            return new CarbonImmutable($data);
        }

        throw new UnexpectedValueException('This value is not a valid date/time.');
    }

    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return $type === CarbonImmutable::class;
    }
}

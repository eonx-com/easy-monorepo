<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Fixture\App\Normalizer;

use EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject\DummyA;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DummyANormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = [])
    {
        throw new UnexpectedValueException('This exception will be handled by API Platform error builders, ' .
            'but default error will be shown.');
    }

    public function getSupportedTypes(?string $format): array
    {
        return [DummyA::class => true];
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return $type === DummyA::class;
    }
}

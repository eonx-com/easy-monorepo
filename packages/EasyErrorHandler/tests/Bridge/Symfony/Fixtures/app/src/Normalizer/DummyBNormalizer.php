<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\Normalizer;

use EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\DataTransferObject\DummyB;
use EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\Exception\DummyBException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DummyBNormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = [])
    {
        throw new DummyBException('This exception will NOT be handled by API Platform error builders');
    }

    public function getSupportedTypes(?string $format): array
    {
        return [DummyB::class => true];
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return $type === DummyB::class;
    }
}

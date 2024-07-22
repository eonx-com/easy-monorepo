<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/Normalizer/DummyANormalizer.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\Normalizer;

use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\DataTransferObject\DummyA;
========
namespace EonX\EasyErrorHandler\Tests\Fixture\App\Normalizer;

use EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject\DummyA;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/Normalizer/DummyANormalizer.php
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DummyANormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = [])
    {
        throw new UnexpectedValueException('This exception will NOT be handled by API Platform error' .
            ' builders, because it message is not supported by them.');
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

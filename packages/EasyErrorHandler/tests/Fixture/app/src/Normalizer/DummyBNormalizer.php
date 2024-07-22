<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/Normalizer/DummyBNormalizer.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\Normalizer;

use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\DataTransferObject\DummyB;
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\Exception\DummyBException;
========
namespace EonX\EasyErrorHandler\Tests\Fixture\App\Normalizer;

use EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject\DummyB;
use EonX\EasyErrorHandler\Tests\Fixture\App\Exception\DummyBException;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/Normalizer/DummyBNormalizer.php
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DummyBNormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = [])
    {
        throw new DummyBException('This exception will NOT be handled by API Platform error builders.');
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

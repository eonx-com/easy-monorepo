<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DenormalizerInterfaceStub implements DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return true;
    }
}

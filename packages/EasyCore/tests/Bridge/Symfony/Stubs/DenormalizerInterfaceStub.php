<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DenormalizerInterfaceStub implements DenormalizerInterface
{
    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param mixed[] $context
     *
     * @return mixed
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        return $data;
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return true;
    }
}

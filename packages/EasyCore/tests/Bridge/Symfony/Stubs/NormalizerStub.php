<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class NormalizerStub implements DenormalizerInterface, NormalizerInterface
{
    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param mixed[]|null $context
     *
     * @return mixed
     */
    public function denormalize($data, $type, $format = null, ?array $context = null)
    {
        return $data;
    }

    /**
     * @param mixed $object
     * @param string|null $format
     * @param mixed[]|null $context
     *
     * @return mixed
     */
    public function normalize($object, $format = null, ?array $context = null)
    {
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return true;
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Normalizers;

use EonX\EasyActivity\Interfaces\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as SymfonyNormalizerInterface;

final class SymfonyNormalizer implements NormalizerInterface
{
    /**
     * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
     */
    private $normalizer;

    public function __construct(SymfonyNormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function normalize($object)
    {
        return $this->normalizer->normalize($object, null, [
            AbstractNormalizer::ATTRIBUTES => ['id'],
        ]);
    }
}

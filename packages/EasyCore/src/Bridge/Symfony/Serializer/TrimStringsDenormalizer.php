<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Serializer;

use EonX\EasyCore\Helpers\StringsTrimmerInterface;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter) Method signatures are defined by parent
 */
final class TrimStringsDenormalizer implements DenormalizerInterface, NormalizerInterface, SerializerAwareInterface
{
    /**
     * @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface
     */
    private $decorated;

    /**
     * A list of array keys whose values will be ignored during processing.
     *
     * @see \EonX\EasyCore\Tests\Helpers\RecursiveStringsTrimmerTest for example
     *
     * @var string[]
     */
    private $except;

    /**
     * @var \EonX\EasyCore\Helpers\StringsTrimmerInterface
     */
    private $trimmer;

    /**
     * @param string[]|null $except
     */
    public function __construct(
        NormalizerInterface $decorated,
        StringsTrimmerInterface $trimmer,
        ?array $except = null
    ) {
        if ($decorated instanceof DenormalizerInterface === false) {
            throw new InvalidArgumentException(
                \sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class)
            );
        }

        $this->trimmer = $trimmer;
        $this->decorated = $decorated;
        $this->except = $except ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, ?array $context = null)
    {
        if (\is_string($data) || \is_array($data)) {
            $data = $this->trimmer->trim($data, $this->except);
        }

        return $this->decorated->denormalize($data, $type, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, ?array $context = null)
    {
        return $this->decorated->normalize($object, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}

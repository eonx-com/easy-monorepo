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
final class TrimStringsNormalizer implements DenormalizerInterface, NormalizerInterface, SerializerAwareInterface
{
    /**
     * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
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
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param mixed[]|null $context
     *
     * @return mixed
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function denormalize($data, $type, $format = null, ?array $context = null)
    {
        if (\is_string($data) || \is_array($data)) {
            $data = $this->trimmer->trim($data, $this->except);
        }
        /** @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $normalizer */
        $normalizer = $this->decorated;

        return $normalizer->denormalize($data, $type, $format, $context ?? []);
    }

    /**
     * @param mixed $object
     * @param string|null $format
     * @param mixed[]|null $context
     *
     * @return mixed
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, $format = null, ?array $context = null)
    {
        return $this->decorated->normalize($object, $format, $context ?? []);
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
        /** @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $normalizer */
        $normalizer = $this->decorated;

        return $normalizer->supportsDenormalization($data, $type, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}

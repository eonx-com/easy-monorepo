<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter) Method signatures are defined by parent
 */
final class TrimStringsNormalizer implements DenormalizerInterface
{
    /**
     * @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface
     */
    private $decorated;

    public function __construct(DenormalizerInterface $decorated)
    {
        if ($decorated instanceof DenormalizerInterface === false) {
            throw new InvalidArgumentException(
                \sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class)
            );
        }

        $this->decorated = $decorated;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $data = $this->clean($data);

        return $this->decorated->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return \is_string($data) || \is_array($data);
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    private function clean($data)
    {
        if ((\is_string($data) || \is_array($data)) !== true) {
            return $data;
        }

        if (\is_array($data) === false) {
            return $this->transform($data);
        }

        return $this->cleanArray($data);
    }

    /**
     * Clean the given value.
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function cleanArray(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->clean($value);
        }

        return $data;
    }

    /**
     * Transform the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function transform($value)
    {
        return \is_string($value) ? \trim($value) : $value;
    }
}

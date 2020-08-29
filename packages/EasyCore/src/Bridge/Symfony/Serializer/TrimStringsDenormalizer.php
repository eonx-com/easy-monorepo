<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Serializer;

use EonX\EasyCore\Helpers\CleanerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter) Method signatures are defined by parent
 */
final class TrimStringsDenormalizer implements DenormalizerInterface
{
    /**
     * @var \EonX\EasyCore\Helpers\CleanerInterface
     */
    private $cleaner;

    /**
     * @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface
     */
    private $decorated;

    /**
     * @var mixed[]
     */
    private $except;

    public function __construct(DenormalizerInterface $decorated, CleanerInterface $cleaner, array $except = [])
    {
        $this->cleaner = $cleaner;
        $this->decorated = $decorated;
        $this->except = $except;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $data = $this->cleaner->clean($data, $this->except);

        return $this->decorated->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return \is_string($data) || \is_array($data);
    }
}

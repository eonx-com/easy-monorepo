<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Serializer;

use EonX\EasyCore\Helpers\StringsTrimmerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter) Method signatures are defined by parent
 */
final class TrimStringsDenormalizer implements DenormalizerInterface
{
    /**
     * @var \EonX\EasyCore\Helpers\StringsTrimmerInterface
     */
    private $cleaner;

    /**
     * @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface
     */
    private $decorated;

    /**
     * A list of array keys whose values will be ignored during processing.
     * @see \EonX\EasyCore\Tests\Helpers\RecursiveStringsTrimmerTest for example
     *
     * @var string[]
     */
    private $except;

    /**
     * @param string[] $except
     */
    public function __construct(
        DenormalizerInterface $decorated,
        StringsTrimmerInterface $cleaner,
        array $except = []
    ) {
        $this->cleaner = $cleaner;
        $this->decorated = $decorated;
        $this->except = $except;
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param mixed[] $context
     *
     * @return mixed
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $data = $this->cleaner->clean($data, $this->except);

        return $this->decorated->denormalize($data, $type, $format, $context);
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return \is_string($data) || \is_array($data);
    }
}

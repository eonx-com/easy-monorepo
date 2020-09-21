<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Serializer;

use EonX\EasyCore\Helpers\StringsTrimmerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter) Method signatures are defined by parent
 */
final class TrimStringsDenormalizer implements DenormalizerAwareInterface, ContextAwareDenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'TRIM_STRINGS_ALREADY_CALLED';

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
    public function __construct(StringsTrimmerInterface $trimmer, ?array $except = null)
    {
        $this->trimmer = $trimmer;
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
        $data = $this->trimmer->trim($data, $this->except);

        $context = $context ?? [];
        $context[self::ALREADY_CALLED] = true;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, ?array $context = null): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return \is_string($data) || \is_array($data);
    }
}

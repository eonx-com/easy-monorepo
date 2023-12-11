<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\Normalizers;

use EonX\EasyUtils\StringTrimmers\StringTrimmerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TrimStringsNormalizer implements DenormalizerInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'TRIM_STRINGS_ALREADY_CALLED';

    /**
     * @var string[]
     */
    private array $exceptKeys;

    /**
     * @param string[]|null $exceptKeys
     */
    public function __construct(
        private StringTrimmerInterface $trimmer,
        ?array $exceptKeys = null,
    ) {
        $this->exceptKeys = $exceptKeys ?? [];
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, ?array $context = null): mixed
    {
        $data = $this->trimmer->trim($data, $this->exceptKeys);

        $context ??= [];
        $context[self::ALREADY_CALLED] = true;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [
            'array' => true,
            'string' => true,
        ];
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        ?array $context = null,
    ): bool {
        $context ??= [];

        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return \is_string($data) || \is_array($data);
    }
}

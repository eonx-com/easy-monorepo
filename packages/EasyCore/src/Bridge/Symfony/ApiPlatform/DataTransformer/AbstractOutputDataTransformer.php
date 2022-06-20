<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;

abstract class AbstractOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @param object|array<string, mixed> $data
     * @param array<string, mixed>|null $context
     *
     * @codeCoverageIgnore
     */
    public function supportsTransformation(mixed $data, string $to, ?array $context = null): bool
    {
        return $to === $this->getOutputDtoClass() && \is_object($data) && $data::class === $this->getApiResourceClass();
    }

    /**
     * @param object $object
     * @param array<string, mixed>|null $context
     */
    public function transform(mixed $object, string $to, ?array $context = null): object
    {
        return $this->doTransform($object, $context);
    }

    /**
     * @param array<string, mixed>|null $context
     */
    abstract protected function doTransform(object $apiResource, ?array $context = null): object;

    abstract protected function getApiResourceClass(): string;

    abstract protected function getOutputDtoClass(): string;
}

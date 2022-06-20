<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractInputDataTransformer implements DataTransformerInterface
{
    private ValidatorInterface $validator;

    #[Required]
    public function setValidator(ValidatorInterface $validator): self
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * @param object|array<string, mixed> $data
     * @param array<string, mixed>|null $context
     *
     * @codeCoverageIgnore
     */
    public function supportsTransformation(mixed $data, string $to, ?array $context = null): bool
    {
        return $to === $this->getApiResourceClass()
            && ($context['input']['class'] ?? null) === $this->getInputDtoClass();
    }

    /**
     * @param object $object
     * @param array<string, mixed>|null $context
     */
    public function transform(mixed $object, string $to, ?array $context = null): object
    {
        $this->validator->validate($object);

        return $this->doTransform($object, $context);
    }

    /**
     * @param array<string, mixed>|null $context
     */
    abstract protected function doTransform(object $dto, ?array $context = null): object;

    abstract protected function getApiResourceClass(): string;

    abstract protected function getInputDtoClass(): string;
}

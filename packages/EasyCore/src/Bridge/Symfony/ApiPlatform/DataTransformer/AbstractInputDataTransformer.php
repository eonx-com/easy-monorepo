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
        $apiResourceClass = $this->getApiResourceClass();

        if ($data instanceof $apiResourceClass) {
            return false;
        }

        return $to === $apiResourceClass && ($context['input']['class'] ?? null) === $this->getInputClass();
    }

    /**
     * @param object $object
     * @param array<string, mixed>|null $context
     */
    public function transform(mixed $object, string $to, ?array $context = null): object
    {
        $this->doValidate($object);

        return $this->doTransform($object, $context);
    }

    /**
     * @param array<string, mixed>|null $context
     */
    abstract protected function doTransform(object $dto, ?array $context = null): object;

    abstract protected function getApiResourceClass(): string;

    abstract protected function getInputClass(): string;

    /**
     * @param object $object
     */
    protected function doValidate(mixed $object): void
    {
        $this->validator->validate($object);
    }
}

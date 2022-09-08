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
        if (is_string($apiResourceClass)) {
            $apiResourceClass = [$apiResourceClass];
        }

        foreach ($apiResourceClass as $class) {
            if ($data instanceof $class) {
                return false;
            }
        }

        $inputClass = $this->getInputClass();
        if (is_string($inputClass)) {
            $inputClass = [$inputClass];
        }

        return in_array($to, $apiResourceClass, true) &&
            in_array($context['input']['class'] ?? null, $inputClass, true);
    }

    /**
     * @param object $object
     * @param array<string, mixed>|null $context
     */
    public function transform(mixed $object, string $to, ?array $context = null): object
    {
        if ($this->isValidationNeeded()) {
            $this->doValidate($object);
        }

        return $this->doTransform($object, $context);
    }

    /**
     * @param array<string, mixed>|null $context
     */
    abstract protected function doTransform(object $object, ?array $context = null): object;

    abstract protected function getApiResourceClass(): array|string;

    abstract protected function getInputClass(): array|string;

    protected function isValidationNeeded(): bool
    {
        return true;
    }

    /**
     * @param object $object
     */
    protected function doValidate(mixed $object): void
    {
        $this->validator->validate($object);
    }
}

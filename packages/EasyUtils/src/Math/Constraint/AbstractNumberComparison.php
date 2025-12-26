<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math\Constraint;

use Symfony\Component\Validator\Constraints\AbstractComparison;

abstract class AbstractNumberComparison extends AbstractComparison
{
    public bool $isMoney;

    public function __construct(
        mixed $value = null,
        ?string $propertyPath = null,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null,
        ?bool $isMoney = null,
    ) {
        $this->isMoney = $isMoney ?? true;

        parent::__construct($value, $propertyPath, $message, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return \str_replace('Constraint', 'Validator', static::class) . 'Validator';
    }
}

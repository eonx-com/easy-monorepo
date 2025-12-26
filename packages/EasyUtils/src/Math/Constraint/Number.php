<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraints\Composite;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Number extends Composite
{
    /**
     * @var \Symfony\Component\Validator\Constraint[]
     */
    public array $constraints = [];

    /**
     * @param \Symfony\Component\Validator\Constraint[]|null $constraints
     * @param string[]|null $groups
     */
    public function __construct(?array $constraints = null, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct($constraints ?? [], $groups, $payload);
    }

    public function getDefaultOption(): string
    {
        return 'constraints';
    }

    public function getRequiredOptions(): array
    {
        return ['constraints'];
    }

    public function validatedBy(): string
    {
        return \str_replace('Constraint', 'Validator', self::class) . 'Validator';
    }

    protected function getCompositeOption(): string
    {
        return 'constraints';
    }
}

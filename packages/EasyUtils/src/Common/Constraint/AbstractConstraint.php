<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Constraint;

use Symfony\Component\Validator\Constraint;

abstract class AbstractConstraint extends Constraint
{
    public function validatedBy(): string
    {
        return \str_replace('Constraint', 'Validator', static::class) . 'Validator';
    }
}

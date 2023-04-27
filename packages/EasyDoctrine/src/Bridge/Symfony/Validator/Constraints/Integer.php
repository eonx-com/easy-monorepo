<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Integer extends Constraint
{
    public string $message = 'integer.not_valid';
}

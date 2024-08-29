<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Constraint;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Integer extends AbstractConstraint
{
    public string $message = 'integer.not_valid';
}

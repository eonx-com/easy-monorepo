<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraints\AbstractComparison;
use Symfony\Component\Validator\Constraints\ZeroComparisonConstraintTrait;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class NumberPositiveOrZero extends AbstractComparison
{
    use ZeroComparisonConstraintTrait;

    public string $message = 'number.should_be_positive_or_zero';
}

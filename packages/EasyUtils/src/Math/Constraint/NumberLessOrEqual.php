<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math\Constraint;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class NumberLessOrEqual extends AbstractNumberComparison
{
    /**
     * @var string
     */
    public $message = 'number.should_be_less_or_equal';

    public function validatedBy(): string
    {
        return \str_replace('Constraint', 'Validator', self::class) . 'Validator';
    }
}

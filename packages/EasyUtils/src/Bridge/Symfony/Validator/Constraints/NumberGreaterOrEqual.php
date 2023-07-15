<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\Validator\Constraints;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class NumberGreaterOrEqual extends AbstractNumberComparison
{
    /**
     * @var string
     */
    public $message = 'number.should_be_greater_or_equal';

    public function validatedBy(): string
    {
        return \str_replace('Constraint', 'Validator', self::class) . 'Validator';
    }
}

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
    public $message = 'This value should be greater than or equal to {compared_value}.';
}

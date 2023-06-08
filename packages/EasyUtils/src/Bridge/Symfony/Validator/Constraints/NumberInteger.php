<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class NumberInteger extends Constraint
{
    public string $message = 'This value should be of type integer.';

    /**
     * @param mixed[] $options
     */
    public function __construct(string $message = null, array $groups = null, $payload = null, array $options = [])
    {
        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
    }
}

<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraints\AbstractComparison;

abstract class AbstractNumberComparison extends AbstractComparison
{
    public bool $isMoney;

    public function __construct(
        mixed $value = null,
        mixed $payload = null,
        ?array $groups = null,
        ?array $options = null,
        ?bool $isMoney = null,
        ?string $message = null,
        ?string $propertyPath = null,
    ) {
        $this->isMoney = $isMoney ?? true;

        parent::__construct($value, $propertyPath, $message, $groups, $payload, $options ?? []);
    }
}
